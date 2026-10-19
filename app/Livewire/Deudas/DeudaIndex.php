<?php

namespace App\Livewire\Deudas;

use App\Models\AbonoDeuda;
use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Deuda;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Deudas y créditos')]
class DeudaIndex extends Component
{
    public bool $mostrarFormulario = false;

    public ?int $deudaId = null;

    public string $tipo = 'por_pagar';

    public string $acreedor_deudor = '';

    public string $concepto = '';

    public string $valor_inicial = '';

    public string $fecha = '';

    public string $fecha_vencimiento = '';

    public string $observacion = '';

    public string $filtroTipo = 'todos';

    public string $filtroEstado = 'todos';

    public string $busqueda = '';

    public bool $mostrarFormularioAbono = false;

    public ?int $abonoDeudaId = null;

    public string $cuentaId = '';

    public string $valorAbono = '';

    public string $fechaAbono = '';

    public string $observacionAbono = '';

    public bool $mostrarHistorial = false;

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
    }

    /**
     * Muestra el formulario para crear una deuda.
     */
    public function crear(): void
    {
        $this->resetFormulario();

        $this->fecha = now()->toDateString();

        $this->mostrarFormulario = true;
    }

    /**
     * Carga una deuda para editarla.
     */
    public function editar(int $id): void
    {
        $deuda = Deuda::findOrFail($id);

        $this->deudaId = $deuda->id;
        $this->tipo = $deuda->tipo;
        $this->acreedor_deudor = $deuda->acreedor_deudor;
        $this->concepto = $deuda->concepto;
        $this->valor_inicial = (string) $deuda->valor_inicial;
        $this->fecha = $deuda->fecha->format('Y-m-d');

        $this->fecha_vencimiento = $deuda->fecha_vencimiento
            ? $deuda->fecha_vencimiento->format('Y-m-d')
            : '';

        $this->observacion = $deuda->observacion ?? '';

        $this->mostrarFormulario = true;
    }

    /**
     * Guarda una deuda nueva o actualiza una existente.
     */
    public function guardar(): void
    {
        $datos = $this->validate([
            'tipo' => [
                'required',
                Rule::in(['por_pagar', 'por_cobrar']),
            ],
            'acreedor_deudor' => [
                'required',
                'string',
                'max:255',
            ],
            'concepto' => [
                'required',
                'string',
                'max:255',
            ],
            'valor_inicial' => [
                'required',
                'integer',
                'min:1',
            ],
            'fecha' => [
                'required',
                'date',
            ],
            'fecha_vencimiento' => [
                'nullable',
                'date',
                'after_or_equal:fecha',
            ],
            'observacion' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $datos['valor_inicial'] = (int) $datos['valor_inicial'];

        $datos['fecha_vencimiento'] =
            $datos['fecha_vencimiento'] ?: null;

        // El estado se calcula automáticamente en el modelo.
        $datos['estado'] = 'pendiente';

        if ($this->deudaId) {
            $deuda = Deuda::withSum('abonos', 'valor')
                ->findOrFail($this->deudaId);

            $totalAbonado = (int) ($deuda->abonos_sum_valor ?? 0);

            if ($datos['valor_inicial'] < $totalAbonado) {
                $this->addError(
                    'valor_inicial',
                    'El valor inicial no puede ser inferior al total abonado.'
                );

                return;
            }

            $deuda->update($datos);
        } else {
            Deuda::create($datos);
        }

        $this->cancelar();

        session()->flash(
            'mensaje',
            'La deuda se guardó correctamente.'
        );
    }

    /**
     * Elimina una deuda que no tenga abonos.
     */
    public function eliminar(int $id): void
    {
        $deuda = Deuda::findOrFail($id);

        if ($deuda->abonos()->exists()) {
            session()->flash(
                'error',
                'No puedes eliminar una deuda que tenga abonos registrados.'
            );

            return;
        }

        $deuda->delete();

        session()->flash(
            'mensaje',
            'La deuda se eliminó correctamente.'
        );
    }

    /**
     * Cancela el formulario de creación o edición.
     */
    public function cancelar(): void
    {
        $this->resetFormulario();

        $this->mostrarFormulario = false;

        $this->resetValidation();
    }

    /**
     * Restablece los campos del formulario de deuda.
     */
    private function resetFormulario(): void
    {
        $this->reset([
            'deudaId',
            'acreedor_deudor',
            'concepto',
            'valor_inicial',
            'fecha_vencimiento',
            'observacion',
        ]);

        $this->tipo = 'por_pagar';

        $this->fecha = now()->toDateString();

        $this->resetValidation();
    }

    /**
     * Registra la apertura del formulario de abono.
     */
    public function registrarAbono(int $id): void
    {
        $deuda = Deuda::findOrFail($id);

        $this->resetValidation();

        $this->abonoDeudaId = $deuda->id;

        $this->cuentaId = '';

        $this->valorAbono = '';

        $this->fechaAbono = now()->toDateString();

        $this->observacionAbono = '';

        $this->mostrarFormularioAbono = true;

        $this->mostrarHistorial = false;
    }

    /**
     * Muestra el historial de abonos de una deuda.
     */
    public function verHistorial(int $id): void
    {
        Deuda::findOrFail($id);

        $this->abonoDeudaId = $id;

        $this->mostrarFormularioAbono = false;

        $this->mostrarHistorial = true;

        $this->resetValidation();
    }

    /**
     * Cancela el formulario de abono.
     */
    public function cancelarAbono(): void
    {
        $this->reset([
            'mostrarFormularioAbono',
            'abonoDeudaId',
            'cuentaId',
            'valorAbono',
            'observacionAbono',
        ]);

        $this->fechaAbono = now()->toDateString();

        $this->resetValidation();
    }

    /**
     * Cierra el historial de abonos.
     */
    public function cerrarHistorial(): void
    {
        $this->mostrarHistorial = false;

        $this->abonoDeudaId = null;
    }

    /**
     * Registra el abono y su movimiento financiero.
     */
    public function guardarAbono(): void
    {
        $datos = $this->validate([
            'abonoDeudaId' => [
                'required',
                'integer',
                'exists:deudas,id',
            ],
            'cuentaId' => [
                'required',
                'integer',
                Rule::exists('cuentas', 'id')
                    ->where(
                        fn ($query) => $query->where('activa', true)
                    ),
            ],
            'valorAbono' => [
                'required',
                'integer',
                'min:1',
            ],
            'fechaAbono' => [
                'required',
                'date',
            ],
            'observacionAbono' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $guardado = DB::transaction(function () use ($datos) {
            $deuda = Deuda::query()
                ->lockForUpdate()
                ->findOrFail($datos['abonoDeudaId']);

            $totalAbonado = (int) $deuda->abonos()->sum('valor');

            $saldoPendiente = max(
                0,
                (int) $deuda->valor_inicial - $totalAbonado
            );

            if ($datos['valorAbono'] > $saldoPendiente) {
                $this->addError(
                    'valorAbono',
                    'El abono no puede superar el saldo pendiente de la deuda.'
                );

                return false;
            }

            if ($saldoPendiente === 0) {
                $this->addError(
                    'valorAbono',
                    'Esta deuda ya está completamente pagada.'
                );

                return false;
            }

            // Una deuda por pagar genera un gasto.
            // Una deuda por cobrar genera un ingreso.
            $tipoMovimiento = $deuda->tipo === 'por_pagar'
                ? 'gasto'
                : 'ingreso';

            $nombreCategoria = $tipoMovimiento === 'gasto'
                ? 'Deudas'
                : 'Otros ingresos';

            $categoriaId = Categoria::query()
                ->where('nombre', $nombreCategoria)
                ->where('tipo', $tipoMovimiento)
                ->value('id');

            $conceptoMovimiento = mb_substr(
                'Abono de deuda: '
                . $deuda->concepto
                . ' - '
                . $deuda->acreedor_deudor,
                0,
                255
            );

            $movimiento = Movimiento::create([
                'cuenta_id' => (int) $datos['cuentaId'],
                'cuenta_destino_id' => null,
                'categoria_id' => $categoriaId,
                'tipo' => $tipoMovimiento,
                'concepto' => $conceptoMovimiento,
                'valor' => (int) $datos['valorAbono'],
                'fecha' => $datos['fechaAbono'],
                'medio_pago' => null,
                'observacion' => $datos['observacionAbono'] ?: null,
            ]);

            AbonoDeuda::create([
                'deuda_id' => $deuda->id,
                'cuenta_id' => (int) $datos['cuentaId'],
                'movimiento_id' => $movimiento->id,
                'valor' => (int) $datos['valorAbono'],
                'fecha' => $datos['fechaAbono'],
                'observacion' => $datos['observacionAbono'] ?: null,
            ]);

            return true;
        });

        if (!$guardado) {
            return;
        }

        $this->cancelarAbono();

        session()->flash(
            'mensaje',
            'El abono se registró correctamente y se actualizó el movimiento financiero.'
        );
    }

    /**
     * Prepara los datos de la vista y aplica los filtros.
     */
    public function render()
    {
        /*
         * Primero consultamos todas las deudas para calcular los totales
         * generales, independientemente de los filtros de la tabla.
         */
        $todasLasDeudas = Deuda::query()
            ->withSum('abonos', 'valor')
            ->get();

        $totalPorPagar = $todasLasDeudas
            ->where('tipo', 'por_pagar')
            ->sum(
                fn (Deuda $deuda) => $deuda->saldo_pendiente
            );

        $totalPorCobrar = $todasLasDeudas
            ->where('tipo', 'por_cobrar')
            ->sum(
                fn (Deuda $deuda) => $deuda->saldo_pendiente
            );

        /*
         * Consulta de las deudas que aparecerán en la tabla.
         * Aquí se aplican los filtros por tipo y búsqueda.
         */
        $deudas = Deuda::query()
            ->withSum('abonos', 'valor')
            ->when(
                $this->filtroTipo !== 'todos',
                fn ($query) => $query->where(
                    'tipo',
                    $this->filtroTipo
                )
            )
            ->when(
                trim($this->busqueda) !== '',
                function ($query) {
                    $busqueda = trim($this->busqueda);

                    $query->where(function ($subquery) use ($busqueda) {
                        $subquery
                            ->where(
                                'acreedor_deudor',
                                'like',
                                '%' . $busqueda . '%'
                            )
                            ->orWhere(
                                'concepto',
                                'like',
                                '%' . $busqueda . '%'
                            );
                    });
                }
            )
            ->orderByDesc('fecha')
            ->get();

        /*
         * El estado se calcula en el modelo Deuda.php.
         * Filtramos la colección usando ese estado calculado.
         */
        if ($this->filtroEstado !== 'todos') {
            $deudas = $deudas
                ->filter(
                    fn (Deuda $deuda) =>
                        $deuda->estado_calculado === $this->filtroEstado
                )
                ->values();
        }

        return view('livewire.deudas.deuda-index', [
            'deudas' => $deudas,

            // Estos totales no cambian al filtrar la tabla.
            'totalPorPagar' => $totalPorPagar,

            'totalPorCobrar' => $totalPorCobrar,

            'cuentas' => Cuenta::where('activa', true)
                ->orderBy('nombre')
                ->get(),

            'deudaAbono' => $this->abonoDeudaId
                ? Deuda::withSum('abonos', 'valor')
                    ->find($this->abonoDeudaId)
                : null,

            'deudaHistorial' => $this->mostrarHistorial
                ? Deuda::withSum('abonos', 'valor')
                    ->find($this->abonoDeudaId)
                : null,

            'abonosHistorial' => $this->mostrarHistorial
                ? AbonoDeuda::with(['cuenta', 'movimiento'])
                    ->where('deuda_id', $this->abonoDeudaId)
                    ->orderByDesc('fecha')
                    ->orderByDesc('id')
                    ->get()
                : collect(),
        ]);
    }
}