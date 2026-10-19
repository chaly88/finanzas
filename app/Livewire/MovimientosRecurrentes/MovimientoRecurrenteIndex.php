<?php

namespace App\Livewire\MovimientosRecurrentes;

use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\MovimientoRecurrente;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Movimientos recurrentes')]
class MovimientoRecurrenteIndex extends Component
{
    public string $tipo = 'gasto';

    public ?int $cuenta_id = null;

    public ?int $categoria_id = null;

    public string $concepto = '';

    public int $valor = 0;

    public string $frecuencia = 'mensual';

    public string $fecha_inicio = '';

    public string $proxima_fecha = '';

    public string $fecha_fin = '';

    public string $medio_pago = '';

    public string $observacion = '';

    public function mount(): void
    {
        $hoy = now()->format('Y-m-d');

        $this->fecha_inicio = $hoy;
        $this->proxima_fecha = $hoy;
    }

    public function registrarMovimiento(int $id): void
    {
        $resultado = DB::transaction(function () use ($id) {
            $programacion = MovimientoRecurrente::query()
                ->lockForUpdate()
                ->findOrFail($id);

            if (!$programacion->activo) {
                return [
                    'ok' => false,
                    'mensaje' => 'La programación está inactiva.',
                ];
            }

            // Usar cadenas Y-m-d para comparar fechas sin horas.
            $hoy = now()->toDateString();

            $fechaInicio = $programacion->fecha_inicio->format('Y-m-d');

            $fechaFin = $programacion->fecha_fin?->format('Y-m-d');

            $proximaFecha = $programacion->proxima_fecha
                ->copy()
                ->startOfDay();

            if ($proximaFecha->toDateString() < $fechaInicio) {
                return [
                    'ok' => false,
                    'mensaje' => 'La próxima fecha prevista es anterior a la fecha de inicio. Corrige la programación antes de registrar movimientos.',
                ];
            }

            // Si la próxima fecha ya supera la fecha final, desactivar.
            if (
                $fechaFin !== null &&
                $proximaFecha->toDateString() > $fechaFin
            ) {
                $programacion->activo = false;
                $programacion->save();

                return [
                    'ok' => true,
                    'mensaje' => 'La programación ha finalizado y se desactivó.',
                ];
            }

            if ($proximaFecha->toDateString() > $hoy) {
                return [
                    'ok' => false,
                    'mensaje' => 'Todavía no ha llegado la próxima fecha prevista.',
                ];
            }

            $registrados = 0;
            $duplicados = 0;

            while (
                $proximaFecha->toDateString() <= $hoy &&
                (
                    $fechaFin === null ||
                    $proximaFecha->toDateString() <= $fechaFin
                )
            ) {
                $fechaMovimiento = $proximaFecha->toDateString();

                $existe = Movimiento::query()
                    ->where('movimiento_recurrente_id', $programacion->id)
                    ->whereDate('fecha', $fechaMovimiento)
                    ->exists();

                if ($existe) {
                    $duplicados++;
                } else {
                    Movimiento::create([
                        'cuenta_id' => $programacion->cuenta_id,
                        'categoria_id' => $programacion->categoria_id,
                        'tipo' => $programacion->tipo,
                        'concepto' => $programacion->concepto,
                        'valor' => $programacion->valor,
                        'fecha' => $fechaMovimiento,
                        'medio_pago' => $programacion->medio_pago,
                        'observacion' => $programacion->observacion,
                        'movimiento_recurrente_id' => $programacion->id,
                    ]);

                    $registrados++;
                }

                $proximaFecha = $this->calcularSiguienteFecha(
                    $programacion,
                    $proximaFecha
                );
            }

            $programacion->proxima_fecha = $proximaFecha;

            // Desactivar solo cuando la siguiente fecha supere la fecha final.
            if (
                $fechaFin !== null &&
                $proximaFecha->toDateString() > $fechaFin
            ) {
                $programacion->activo = false;
            }

            $programacion->save();

            $mensaje = "{$registrados} movimiento(s) registrado(s).";

            if ($duplicados > 0) {
                $mensaje .= " Se omitieron {$duplicados} vencimiento(s) ya registrado(s).";
            }

            if (!$programacion->activo) {
                $mensaje .= ' La programación ha finalizado y se desactivó.';
            } else {
                $mensaje .= ' Próxima fecha: '
                    . $proximaFecha->format('d/m/Y')
                    . '.';
            }

            return [
                'ok' => true,
                'mensaje' => $mensaje,
            ];
        });

        session()->flash(
            $resultado['ok'] ? 'mensaje' : 'error',
            $resultado['mensaje']
        );
    }

    public function updatedTipo(): void
    {
        $this->categoria_id = null;
    }

    private function calcularSiguienteFecha(
        MovimientoRecurrente $programacion,
        Carbon $fechaActual
    ): Carbon {
        return match ($programacion->frecuencia) {
            'semanal' => $fechaActual->copy()->addWeek(),

            'quincenal' => $fechaActual->copy()->addDays(15),

            'mensual' => $this->calcularSiguienteFechaMensual(
                $programacion,
                $fechaActual
            ),

            'trimestral' => $this->calcularSiguienteFechaTrimestral(
                $programacion,
                $fechaActual
            ),

            'anual' => $this->calcularSiguienteFechaAnual(
                $programacion,
                $fechaActual
            ),

            default => throw new \InvalidArgumentException(
                'La frecuencia de la programación no es válida.'
            ),
        };
    }

    private function calcularSiguienteFechaTrimestral(
        MovimientoRecurrente $programacion,
        Carbon $fechaActual
    ): Carbon {
        $fechaInicio = $programacion->fecha_inicio;

        $inicioEsFinDeMes = $fechaInicio->day ===
            $fechaInicio->copy()->endOfMonth()->day;

        $siguienteTrimestre = $fechaActual->copy()
            ->startOfMonth()
            ->addMonthsNoOverflow(3);

        if ($inicioEsFinDeMes) {
            return $siguienteTrimestre->endOfMonth();
        }

        $diaOriginal = $fechaInicio->day;

        $diaDisponible = min(
            $diaOriginal,
            $siguienteTrimestre->daysInMonth
        );

        return $siguienteTrimestre->day($diaDisponible);
    }

    private function calcularSiguienteFechaMensual(
        MovimientoRecurrente $programacion,
        Carbon $fechaActual
    ): Carbon {
        $fechaInicio = $programacion->fecha_inicio;

        $inicioEsFinDeMes = $fechaInicio->day ===
            $fechaInicio->copy()->endOfMonth()->day;

        $siguienteMes = $fechaActual->copy()
            ->startOfMonth()
            ->addMonthNoOverflow();

        if ($inicioEsFinDeMes) {
            return $siguienteMes->endOfMonth();
        }

        $diaOriginal = $fechaInicio->day;

        $diaDisponible = min(
            $diaOriginal,
            $siguienteMes->daysInMonth
        );

        return $siguienteMes->day($diaDisponible);
    }

    private function calcularSiguienteFechaAnual(
        MovimientoRecurrente $programacion,
        Carbon $fechaActual
    ): Carbon {
        $fechaInicio = $programacion->fecha_inicio;

        $diaOriginal = $fechaInicio->day;
        $mesOriginal = $fechaInicio->month;

        $siguienteAnio = $fechaActual->year + 1;

        $fechaObjetivo = Carbon::create(
            $siguienteAnio,
            $mesOriginal,
            1
        );

        $diaDisponible = min(
            $diaOriginal,
            $fechaObjetivo->daysInMonth
        );

        return $fechaObjetivo->day($diaDisponible);
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'tipo' => [
                'required',
                Rule::in(['ingreso', 'gasto']),
            ],
            'cuenta_id' => [
                'required',
                'integer',
                'exists:cuentas,id',
            ],
            'categoria_id' => [
                'nullable',
                'integer',
                'exists:categorias,id',
            ],
            'concepto' => [
                'required',
                'string',
                'max:150',
            ],
            'valor' => [
                'required',
                'integer',
                'min:1',
            ],
            'frecuencia' => [
                'required',
                Rule::in([
                    'semanal',
                    'quincenal',
                    'mensual',
                    'trimestral',
                    'anual',
                ]),
            ],
            'fecha_inicio' => [
                'required',
                'date',
            ],
            'proxima_fecha' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'fecha_fin' => [
                'nullable',
                'date',
                'after_or_equal:proxima_fecha',
            ],
            'medio_pago' => [
                'nullable',
                'string',
                'max:30',
            ],
            'observacion' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $cuentaActiva = Cuenta::query()
            ->whereKey($datos['cuenta_id'])
            ->where('activa', true)
            ->exists();

        if (!$cuentaActiva) {
            $this->addError(
                'cuenta_id',
                'Selecciona una cuenta activa.'
            );

            return;
        }

        if ($datos['categoria_id']) {
            $categoriaValida = Categoria::query()
                ->whereKey($datos['categoria_id'])
                ->where('tipo', $datos['tipo'])
                ->where('activa', true)
                ->exists();

            if (!$categoriaValida) {
                $this->addError(
                    'categoria_id',
                    'Selecciona una categoría activa del tipo correspondiente.'
                );

                return;
            }
        }

        MovimientoRecurrente::create([
            'cuenta_id' => $datos['cuenta_id'],
            'categoria_id' => $datos['categoria_id'] ?: null,
            'tipo' => $datos['tipo'],
            'concepto' => $datos['concepto'],
            'valor' => $datos['valor'],
            'frecuencia' => $datos['frecuencia'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'proxima_fecha' => $datos['proxima_fecha'],
            'fecha_fin' => $datos['fecha_fin'] ?: null,
            'medio_pago' => $datos['medio_pago'] ?: null,
            'observacion' => $datos['observacion'] ?: null,
            'activo' => true,
        ]);

        session()->flash(
            'mensaje',
            'Programación recurrente creada correctamente.'
        );

        $this->cancelar();
    }


    public function cambiarEstado(int $id): void
    {
        $programacion = MovimientoRecurrente::findOrFail($id);

        // Si está activa, permitir desactivarla.
        if ($programacion->activo) {
            $programacion->activo = false;
            $programacion->save();

            session()->flash(
                'mensaje',
                'Programación desactivada correctamente.'
            );

            return;
        }

        // No reactivar si la fecha final ya pasó.
        $hoy = now()->toDateString();

        $fechaFin = $programacion->fecha_fin?->format('Y-m-d');
        $proximaFecha = $programacion->proxima_fecha->format('Y-m-d');
        $fechaInicio = $programacion->fecha_inicio->format('Y-m-d');

        if ($proximaFecha < $fechaInicio) {
            session()->flash(
                'error',
                'No se puede activar: la próxima fecha es anterior a la fecha de inicio.'
            );

            return;
        }

        if ($fechaFin !== null && $fechaFin < $hoy) {
            session()->flash(
                'error',
                'No se puede reactivar: la fecha final de la programación ya pasó.'
            );

            return;
        }

        if ($fechaFin !== null && $proximaFecha > $fechaFin) {
            session()->flash(
                'error',
                'No se puede reactivar: la próxima fecha supera la fecha final.'
            );

            return;
        }

        $programacion->activo = true;
        $programacion->save();

        session()->flash(
            'mensaje',
            'Programación activada correctamente.'
        );
    }

    public function cancelar(): void
    {
        $this->reset([
            'cuenta_id',
            'categoria_id',
            'concepto',
            'valor',
            'fecha_fin',
            'medio_pago',
            'observacion',
        ]);

        $this->tipo = 'gasto';
        $this->frecuencia = 'mensual';

        $hoy = now()->format('Y-m-d');

        $this->fecha_inicio = $hoy;
        $this->proxima_fecha = $hoy;

        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.movimientos-recurrentes.movimiento-recurrente-index',
            [
                'cuentas' => Cuenta::query()
                    ->where('activa', true)
                    ->orderBy('nombre')
                    ->get(),

                'categorias' => Categoria::query()
                    ->where('activa', true)
                    ->where('tipo', $this->tipo)
                    ->orderBy('nombre')
                    ->get(),

                'programaciones' => MovimientoRecurrente::query()
                    ->with(['cuenta', 'categoria'])
                    ->orderByDesc('activo')
                    ->orderBy('proxima_fecha')
                    ->orderBy('concepto')
                    ->get(),
            ]
        );
    }
}
