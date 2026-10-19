<?php

namespace App\Livewire\Ahorros;

use App\Models\MetaAhorro;
use App\Models\MovimientoAhorro;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cuenta;
use App\Models\Movimiento;

#[Layout('layouts.app')]
#[Title('Ahorros y Metas')]
class MetaIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public bool $mostrarFormulario = false;
    public bool $mostrarFormularioMovimiento = false;
    public bool $mostrarHistorial = false;

    public ?int $metaEditandoId = null;
    public ?int $metaAhorroId = null;
    public ?int $metaHistorialId = null;

    public string $nombre = '';
    public string $descripcion = '';
    public string $monto_objetivo = '';
    public string $fecha_inicio = '';
    public string $fecha_objetivo = '';
    public string $prioridad = 'media';

    public string $nombreMetaMovimiento = '';
    public string $nombreMetaHistorial = '';
    public string $tipoMovimiento = 'aporte';
    public string $montoMovimiento = '';
    public string $fechaMovimiento = '';
    public string $descripcionMovimiento = '';
    public ?int $cuentaOrigenId = null;
    public ?int $cuentaDestinoId = null;
    public function mount(): void
    {
        $this->fecha_inicio = now()->format('Y-m-d');
        $this->fechaMovimiento = now()->format('Y-m-d');
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Crear y editar metas
    |--------------------------------------------------------------------------
    */

    public function crear(): void
    {
        $this->cancelarMovimiento();
        $this->cerrarHistorial();
        $this->limpiarFormulario();

        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $meta = MetaAhorro::findOrFail($id);

        if ($meta->estado !== 'activa') {
            session()->flash(
                'error',
                'Solo puedes editar metas que estén activas.'
            );

            return;
        }

        $this->cancelarMovimiento();
        $this->cerrarHistorial();
        $this->limpiarFormulario();

        $this->metaEditandoId = $meta->id;
        $this->nombre = $meta->nombre;
        $this->descripcion = $meta->descripcion ?? '';
        $this->monto_objetivo = (string) $meta->monto_objetivo;
        $this->fecha_inicio = $meta->fecha_inicio->format('Y-m-d');
        $this->fecha_objetivo =
            $meta->fecha_objetivo?->format('Y-m-d') ?? '';
        $this->prioridad = $meta->prioridad;

        $this->mostrarFormulario = true;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'monto_objetivo' => [
                'required',
                'integer',
                'min:1',
                'max:9999999999999',
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_objetivo' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio',
            ],
            'prioridad' => [
                'required',
                Rule::in(['baja', 'media', 'alta']),
            ],
        ], [
            'nombre.required' => 'Escribe el nombre de la meta.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'monto_objetivo.required' => 'Indica el monto que deseas ahorrar.',
            'monto_objetivo.numeric' => 'El monto debe ser un número válido.',
            'monto_objetivo.gt' => 'El monto objetivo debe ser mayor que cero.',
            'fecha_inicio.required' => 'Selecciona la fecha de inicio.',
            'fecha_objetivo.after_or_equal' =>
                'La fecha objetivo no puede ser anterior a la fecha de inicio.',
            'prioridad.in' => 'Selecciona una prioridad válida.',
        ]);

        $datos['nombre'] = trim($datos['nombre']);
        $datos['descripcion'] = trim($datos['descripcion'] ?? '') ?: null;
        $datos['fecha_objetivo'] = $datos['fecha_objetivo'] ?: null;

        if ($this->metaEditandoId !== null) {
            $meta = MetaAhorro::findOrFail($this->metaEditandoId);

            if ($meta->estado !== 'activa') {
                session()->flash(
                    'error',
                    'Solo puedes editar metas que estén activas.'
                );

                return;
            }

            $meta->update($datos);

            $mensaje = 'La meta se actualizó correctamente.';
        } else {
            $datos['estado'] = 'activa';

            MetaAhorro::create($datos);

            $mensaje = 'La meta de ahorro se creó correctamente.';
        }

        $this->cancelar();

        session()->flash('mensaje', $mensaje);
    }

    public function cancelar(): void
    {
        $this->limpiarFormulario();
        $this->mostrarFormulario = false;
        $this->resetValidation();
    }

    private function limpiarFormulario(): void
    {
        $this->reset([
            'metaEditandoId',
            'nombre',
            'descripcion',
            'monto_objetivo',
            'fecha_objetivo',
        ]);

        $this->fecha_inicio = now()->format('Y-m-d');
        $this->prioridad = 'media';

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Aportes y retiros
    |--------------------------------------------------------------------------
    */

    public function abrirMovimiento(int $metaId, string $tipo): void
    {
        if (!in_array($tipo, ['aporte', 'retiro'], true)) {
            abort(404);
        }

        $meta = MetaAhorro::findOrFail($metaId);

        if ($meta->estado !== 'activa') {
            session()->flash(
                'error',
                'Solo puedes registrar movimientos en metas activas.'
            );

            return;
        }

        $this->cancelar();
        $this->cerrarHistorial();
        $this->cancelarMovimiento();

        $this->metaAhorroId = $meta->id;
        $this->nombreMetaMovimiento = $meta->nombre;
        $this->tipoMovimiento = $tipo;
        $this->mostrarFormularioMovimiento = true;
    }


    public function guardarMovimiento(): void
    {
        $datos = $this->validate([
            'metaAhorroId' => [
                'required',
                'integer',
                'exists:metas_ahorro,id',
            ],
            'tipoMovimiento' => [
                'required',
                Rule::in(['aporte', 'retiro']),
            ],
            'montoMovimiento' => [
                'required',
                'integer',
                'min:1',
                'max:9999999999999',
            ],
            'fechaMovimiento' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'descripcionMovimiento' => [
                'nullable',
                'string',
                'max:255',
            ],
            'cuentaOrigenId' => [
                'required',
                'integer',
                'exists:cuentas,id',
                'different:cuentaDestinoId',
            ],
            'cuentaDestinoId' => [
                'required',
                'integer',
                'exists:cuentas,id',
                'different:cuentaOrigenId',
            ],
        ], [
            'montoMovimiento.integer' =>
                'El monto debe expresarse en pesos enteros.',
            'montoMovimiento.min' =>
                'El monto debe ser de al menos un peso.',
            'fechaMovimiento.before_or_equal' =>
                'La fecha no puede ser futura.',
            'cuentaOrigenId.required' =>
                'Selecciona la cuenta de origen.',
            'cuentaDestinoId.required' =>
                'Selecciona la cuenta de destino.',
            'cuentaOrigenId.different' =>
                'La cuenta de origen y destino deben ser diferentes.',
            'cuentaDestinoId.different' =>
                'La cuenta de origen y destino deben ser diferentes.',
        ]);

        $monto = (int) $datos['montoMovimiento'];

        DB::transaction(function () use ($datos, $monto) {
            $meta = MetaAhorro::whereKey($datos['metaAhorroId'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($meta->estado !== 'activa') {
                throw ValidationException::withMessages([
                    'montoMovimiento' =>
                        'Esta meta ya no está activa.',
                ]);
            }

            // Bloquear las cuentas en orden para reducir conflictos.
            $cuentas = Cuenta::whereIn('id', [
                $datos['cuentaOrigenId'],
                $datos['cuentaDestinoId'],
            ])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $origen = $cuentas->get((int) $datos['cuentaOrigenId']);
            $destino = $cuentas->get((int) $datos['cuentaDestinoId']);

            if (!$origen || !$destino) {
                throw ValidationException::withMessages([
                    'cuentaOrigenId' =>
                        'No se encontraron las cuentas seleccionadas.',
                ]);
            }

            if (!$origen->activa) {
                throw ValidationException::withMessages([
                    'cuentaOrigenId' =>
                        'La cuenta de origen está inactiva.',
                ]);
            }

            if (!$destino->activa) {
                throw ValidationException::withMessages([
                    'cuentaDestinoId' =>
                        'La cuenta de destino está inactiva.',
                ]);
            }

            // No permitir que la cuenta de origen quede en negativo.
            $saldoCuenta = (int) $origen->saldo_actual;

            if ($monto > $saldoCuenta) {
                throw ValidationException::withMessages([
                    'montoMovimiento' =>
                        'La cuenta de origen solo tiene disponible $'
                        . number_format($saldoCuenta, 0, ',', '.')
                        . '.',
                ]);
            }

            $aportado = (float) $meta->movimientos()
                ->where('tipo', 'aporte')
                ->sum('monto');

            $retirado = (float) $meta->movimientos()
                ->where('tipo', 'retiro')
                ->sum('monto');

            $saldoMeta = round($aportado - $retirado, 2);

            if (
                $datos['tipoMovimiento'] === 'retiro'
                && $monto > $saldoMeta
            ) {
                throw ValidationException::withMessages([
                    'montoMovimiento' =>
                        'El retiro supera el saldo de la meta. '
                        . 'Disponible: $'
                        . number_format($saldoMeta, 0, ',', '.')
                        . '.',
                ]);
            }

            $concepto = $datos['tipoMovimiento'] === 'aporte'
                ? 'Aporte a meta: ' . $meta->nombre
                : 'Retiro de meta: ' . $meta->nombre;

            $descripcion = trim(
                $datos['descripcionMovimiento'] ?? ''
            );

            $movimiento = Movimiento::create([
                'cuenta_id' => $origen->id,
                'cuenta_destino_id' => $destino->id,
                'tipo' => 'transferencia',
                'concepto' => mb_substr($concepto, 0, 150),
                'valor' => $monto,
                'fecha' => $datos['fechaMovimiento'],
                'medio_pago' => null,
                'observacion' => $descripcion ?: null,
                'categoria_id' => null,
            ]);

            $meta->movimientos()->create([
                'movimiento_id' => $movimiento->id,
                'tipo' => $datos['tipoMovimiento'],
                'monto' => $monto,
                'fecha' => $datos['fechaMovimiento'],
                'descripcion' => $descripcion ?: null,
            ]);
        });

        $tipoTexto = $datos['tipoMovimiento'] === 'aporte'
            ? 'aporte'
            : 'retiro';

        $this->cancelarMovimiento();

        session()->flash(
            'mensaje',
            'El ' . $tipoTexto
            . ' se registró correctamente como transferencia entre cuentas.'
        );
    }


    public function cancelarMovimiento(): void
    {
        $this->limpiarFormularioMovimiento();
        $this->mostrarFormularioMovimiento = false;
        $this->resetValidation();
    }

    private function limpiarFormularioMovimiento(): void
    {
        $this->reset([
            'metaAhorroId',
            'nombreMetaMovimiento',
            'montoMovimiento',
            'descripcionMovimiento',
            'cuentaOrigenId',
            'cuentaDestinoId',
        ]);

        $this->tipoMovimiento = 'aporte';
        $this->fechaMovimiento = now()->format('Y-m-d');

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Historial de movimientos
    |--------------------------------------------------------------------------
    */

    public function verHistorial(int $metaId): void
    {
        $meta = MetaAhorro::findOrFail($metaId);

        $this->cancelar();
        $this->cancelarMovimiento();

        $this->metaHistorialId = $meta->id;
        $this->nombreMetaHistorial = $meta->nombre;
        $this->mostrarHistorial = true;

        $this->resetPage('paginaHistorial');
    }

    public function cerrarHistorial(): void
    {
        $this->mostrarHistorial = false;
        $this->metaHistorialId = null;
        $this->nombreMetaHistorial = '';

        $this->resetPage('paginaHistorial');
    }

    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    */
    public function marcarCumplida(int $id): void
    {
        $meta = MetaAhorro::findOrFail($id);

        if ($meta->estado !== 'activa') {
            session()->flash(
                'error',
                'Solo puedes completar metas que estén activas.'
            );

            return;
        }

        $saldo = round(
            (float) $meta->movimientos()
                ->where('tipo', 'aporte')
                ->sum('monto')
            -
            (float) $meta->movimientos()
                ->where('tipo', 'retiro')
                ->sum('monto'),
            2
        );

        $objetivo = (float) $meta->monto_objetivo;

        if ($saldo < $objetivo) {
            $faltante = $objetivo - $saldo;

            session()->flash(
                'error',
                'No puedes marcar esta meta como cumplida. '
                . 'Todavía faltan $'
                . number_format($faltante, 2, ',', '.')
                . ' para alcanzar el objetivo.'
            );

            return;
        }

        $meta->update(['estado' => 'cumplida']);

        session()->flash(
            'mensaje',
            'La meta se marcó como cumplida correctamente.'
        );
    }

    public function cancelarMeta(int $id): void
    {
        $meta = MetaAhorro::findOrFail($id);

        if ($meta->estado !== 'activa') {
            session()->flash(
                'error',
                'Solo puedes cancelar metas que estén activas.'
            );

            return;
        }

        $meta->update(['estado' => 'cancelada']);

        session()->flash(
            'mensaje',
            'La meta se canceló correctamente. Su historial se conserva.'
        );
    }

    public function reactivarMeta(int $id): void
    {
        $meta = MetaAhorro::findOrFail($id);

        if ($meta->estado !== 'cancelada') {
            session()->flash(
                'error',
                'Solo puedes reactivar metas que estén canceladas.'
            );

            return;
        }

        $meta->update(['estado' => 'activa']);

        session()->flash(
            'mensaje',
            'La meta se reactivó correctamente.'
        );
    }
    public function render()
    {
        $metas = MetaAhorro::query()
            ->when(
                trim($this->busqueda) !== '',
                fn($query) => $query->where(
                    'nombre',
                    'like',
                    '%' . trim($this->busqueda) . '%'
                )
            )
            ->orderByRaw("
                CASE estado
                    WHEN 'activa' THEN 1
                    WHEN 'cumplida' THEN 2
                    WHEN 'cancelada' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('id')
            ->paginate(10);

        $movimientosHistorial = MovimientoAhorro::query()
            ->when(
                $this->metaHistorialId !== null,
                fn($query) => $query->where(
                    'meta_ahorro_id',
                    $this->metaHistorialId
                ),
                fn($query) => $query->whereRaw('1 = 0')
            )
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'paginaHistorial');

        return view('livewire.ahorros.meta-index', [
            'metas' => $metas,
            'movimientosHistorial' => $movimientosHistorial,
            'cuentas' => Cuenta::query()
                ->where('activa', true)
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
