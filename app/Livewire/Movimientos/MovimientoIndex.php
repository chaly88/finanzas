<?php

namespace App\Livewire\Movimientos;

use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Movimientos')]
class MovimientoIndex extends Component
{
    public string $tipo = 'gasto';

    public ?int $cuenta_id = null;

    public ?int $cuenta_destino_id = null;

    public string $concepto = '';

    public int $valor = 0;

    public string $fecha = '';

    public string $medio_pago = '';

    public string $observacion = '';

    public ?int $categoria_id = null;

    #[Locked]
    public ?int $movimientoEditandoId = null;

    public bool $confirmarGastoSinSaldo = false;

    public int $saldoDisponibleGasto = 0;

    public int $saldoResultanteGasto = 0;

    #[Locked]
    public ?string $firmaConfirmacionGasto = null;

    public function mount(): void
    {
        $this->fecha = now()->format('Y-m-d');
    }
    public function cancelarConfirmacionGasto(): void
    {
        $this->limpiarConfirmacionGasto();
    }
    /**
     * Al cambiar el tipo, limpiar campos que podrían
     * pertenecer al tipo de movimiento anterior.
     */
    public function updatedTipo(): void
    {
        $this->categoria_id = null;

        if ($this->tipo !== 'transferencia') {
            $this->cuenta_destino_id = null;
        }
    }

    /**
     * Invalidar la confirmación pendiente cuando cambia
     * cualquier dato del formulario.
     */
    public function updated($property): void
    {
        $camposFormulario = [
            'tipo',
            'cuenta_id',
            'cuenta_destino_id',
            'categoria_id',
            'concepto',
            'valor',
            'fecha',
            'medio_pago',
            'observacion',
        ];

        if (in_array($property, $camposFormulario, true)) {
            $this->limpiarConfirmacionGasto();
        }
    }

    /**
     * Limpiar la advertencia y los saldos calculados.
     */
    private function limpiarConfirmacionGasto(): void
    {
        $this->confirmarGastoSinSaldo = false;
        $this->saldoDisponibleGasto = 0;
        $this->saldoResultanteGasto = 0;
        $this->firmaConfirmacionGasto = null;
    }

    /**
     * Cargar un movimiento existente en el formulario.
     */
    public function editar(int $id): void
    {
        $this->resetValidation();
        $this->limpiarConfirmacionGasto();

        $movimiento = Movimiento::query()
            ->with([
                'abonoDeuda',
                'movimientoAhorro',
                'movimientoRecurrente',
            ])
            ->findOrFail($id);

        if (!$this->esEditable($movimiento)) {
            $this->addError(
                'movimientoEditandoId',
                'Este movimiento está vinculado a una deuda, ahorro o '
                . 'programación recurrente. Debes gestionarlo desde su módulo.'
            );

            return;
        }

        $this->movimientoEditandoId = $movimiento->id;

        $this->tipo = $movimiento->tipo;
        $this->cuenta_id = $movimiento->cuenta_id;
        $this->cuenta_destino_id = $movimiento->cuenta_destino_id;
        $this->categoria_id = $movimiento->categoria_id;
        $this->concepto = $movimiento->concepto;
        $this->valor = (int) $movimiento->valor;
        $this->fecha = $movimiento->fecha->format('Y-m-d');
        $this->medio_pago = $movimiento->medio_pago ?? '';
        $this->observacion = $movimiento->observacion ?? '';
    }

    /**
     * Registrar un movimiento nuevo o actualizar uno existente.
     */
    public function guardar(bool $confirmarSinSaldo = false): void
    {
        
    if (
        $confirmarSinSaldo
        && (
            $this->tipo !== 'gasto'
            || !$this->confirmarGastoSinSaldo
            || $this->firmaConfirmacionGasto === null
        )
    ) {
        $this->limpiarConfirmacionGasto();

        $this->addError(
            'valor',
            'La confirmación del gasto ya no es válida. '
            . 'Revisa los datos y vuelve a guardar.'
        );

        return;
    }

        $datos = $this->validate([
            'tipo' => [
                'required',
                Rule::in(['ingreso', 'gasto', 'transferencia']),
            ],
            'cuenta_id' => [
                'required',
                'integer',
                'exists:cuentas,id',
            ],
            'cuenta_destino_id' => [
                'nullable',
                'integer',
                'exists:cuentas,id',
                'different:cuenta_id',
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
            'fecha' => [
                'required',
                'date',
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

        /*
         * Normalizar campos según el tipo.
         */
        if ($datos['tipo'] === 'transferencia') {
            if (empty($datos['cuenta_destino_id'])) {
                throw ValidationException::withMessages([
                    'cuenta_destino_id' =>
                        'Selecciona la cuenta de destino.',
                ]);
            }

            $datos['categoria_id'] = null;
        } else {
            $datos['cuenta_destino_id'] = null;

            if (!empty($datos['categoria_id'])) {
                $categoria = Categoria::findOrFail(
                    $datos['categoria_id']
                );

                if (
                    !$categoria->activa
                    || $categoria->tipo !== $datos['tipo']
                ) {
                    throw ValidationException::withMessages([
                        'categoria_id' =>
                            'Selecciona una categoría activa del tipo '
                            . 'de movimiento correcto.',
                    ]);
                }
            }
        }

        $idEditando = $this->movimientoEditandoId;

        $resultado = DB::transaction(function () use ($datos, $idEditando, $confirmarSinSaldo) {
            $movimiento = null;

            if ($idEditando !== null) {
                $movimiento = Movimiento::query()
                    ->whereKey($idEditando)
                    ->lockForUpdate()
                    ->firstOrFail();

                $movimiento->load([
                    'abonoDeuda',
                    'movimientoAhorro',
                    'movimientoRecurrente',
                ]);

                if (!$this->esEditable($movimiento)) {
                    throw ValidationException::withMessages([
                        'movimientoEditandoId' =>
                            'Este movimiento está vinculado a otro módulo '
                            . 'y no puede editarse desde aquí.',
                    ]);
                }
            }

            /*
             * Bloquear las cuentas implicadas en orden de ID.
             * Al editar, también incluimos las cuentas originales.
             */
            $idsCuentas = [(int) $datos['cuenta_id']];

            if ($datos['tipo'] === 'transferencia') {
                $idsCuentas[] = (int) $datos['cuenta_destino_id'];
            }

            if ($movimiento !== null) {
                $idsCuentas[] = (int) $movimiento->cuenta_id;

                if ($movimiento->cuenta_destino_id !== null) {
                    $idsCuentas[] = (int) $movimiento->cuenta_destino_id;
                }
            }

            $idsCuentas = array_values(array_unique($idsCuentas));

            $cuentas = Cuenta::query()
                ->whereIn('id', $idsCuentas)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $origen = $cuentas->get((int) $datos['cuenta_id']);

            if (!$origen || !$origen->activa) {
                throw ValidationException::withMessages([
                    'cuenta_id' =>
                        'Selecciona una cuenta de origen activa.',
                ]);
            }

            /*
             * Advertir si el gasto supera el saldo disponible.
             * Al editar, se retira del cálculo el efecto del movimiento
             * original para evitar descontarlo dos veces.
             */

if ($datos['tipo'] === 'gasto') {
    $saldoDisponible = (int) $origen->saldo_actual;

    if ($movimiento !== null) {
        $saldoDisponible -= $this->contribucionEnCuenta(
            $movimiento,
            (int) $origen->id
        );
    }

    $saldoResultante = $saldoDisponible - (int) $datos['valor'];

    if ($saldoResultante < 0) {
        $firmaActual = $this->generarFirmaConfirmacionGasto(
            $datos,
            $idEditando,
            $saldoDisponible,
            $saldoResultante
        );

        $confirmacionValida =
            $confirmarSinSaldo
            && $this->confirmarGastoSinSaldo
            && $this->firmaConfirmacionGasto !== null
            && hash_equals(
                $this->firmaConfirmacionGasto,
                $firmaActual
            );

        if (!$confirmacionValida) {
            return [
                'requiere_confirmacion' => true,
                'saldo_disponible' => $saldoDisponible,
                'saldo_resultante' => $saldoResultante,
            ];
        }
    }
}


            if ($datos['tipo'] === 'transferencia') {
                $destino = $cuentas->get(
                    (int) $datos['cuenta_destino_id']
                );

                if (!$destino || !$destino->activa) {
                    throw ValidationException::withMessages([
                        'cuenta_destino_id' =>
                            'Selecciona una cuenta de destino activa.',
                    ]);
                }

                /*
                 * El saldo actual ya incluye el movimiento original.
                 * Para validar la transferencia editada, retiramos
                 * primero el efecto que ese movimiento tiene sobre
                 * la cuenta que ahora se seleccionó como origen.
                 */
                $saldoDisponible = (int) $origen->saldo_actual;

                if ($movimiento !== null) {
                    $saldoDisponible -=
                        $this->contribucionEnCuenta(
                            $movimiento,
                            (int) $origen->id
                        );
                }

                if ((int) $datos['valor'] > $saldoDisponible) {
                    throw ValidationException::withMessages([
                        'valor' =>
                            'Saldo insuficiente. Disponible: $'
                            . number_format(
                                max(0, $saldoDisponible),
                                0,
                                ',',
                                '.'
                            )
                            . '.',
                    ]);
                }
            }

            /*
             * Para un registro nuevo se crea el movimiento.
             * Para uno existente se actualizan sus campos.
             *
             * Los saldos se calculan a partir de los movimientos,
             * por lo que no se modifican manualmente.
             */
            if ($movimiento === null) {
                $movimiento = new Movimiento();
            }

            $movimiento->fill($datos);
            $movimiento->save();
        });

if ($resultado['requiere_confirmacion'] ?? false) {
    $this->saldoDisponibleGasto =
        $resultado['saldo_disponible'];

    $this->saldoResultanteGasto =
        $resultado['saldo_resultante'];

    $this->firmaConfirmacionGasto =
        $this->generarFirmaConfirmacionGasto(
            $datos,
            $idEditando,
            $this->saldoDisponibleGasto,
            $this->saldoResultanteGasto
        );

    $this->confirmarGastoSinSaldo = true;

    return;
}

        session()->flash(
            'mensaje',
            $idEditando !== null
            ? 'Movimiento actualizado correctamente.'
            : 'Movimiento registrado correctamente.'
        );

        $this->cancelarEdicion();
    }

    /**
     * Determinar si el movimiento puede editarse aquí.
     */
    private function esEditable(Movimiento $movimiento): bool
    {
        return !$movimiento->abonoDeuda
            && !$movimiento->movimientoAhorro
            && !$movimiento->movimientoRecurrente;
    }

    /**
     * Calcular cuánto aporta un movimiento al saldo de una cuenta:
     * ingreso = positivo; gasto = negativo;
     * transferencia = negativo en origen y positivo en destino.
     */
    private function contribucionEnCuenta(
        Movimiento $movimiento,
        int $cuentaId
    ): int {
        $valor = (int) $movimiento->valor;

        if (
            $movimiento->tipo === 'ingreso'
            && (int) $movimiento->cuenta_id === $cuentaId
        ) {
            return $valor;
        }

        if (
            $movimiento->tipo === 'gasto'
            && (int) $movimiento->cuenta_id === $cuentaId
        ) {
            return -$valor;
        }

        if ($movimiento->tipo === 'transferencia') {
            $contribucion = 0;

            if ((int) $movimiento->cuenta_id === $cuentaId) {
                $contribucion -= $valor;
            }

            if ((int) $movimiento->cuenta_destino_id === $cuentaId) {
                $contribucion += $valor;
            }

            return $contribucion;
        }

        return 0;
    }

    /**
     * Generar una firma para identificar la confirmación
     * de un gasto con saldo negativo.
     */
    private function generarFirmaConfirmacionGasto(
        array $datos,
        ?int $idEditando,
        int $saldoDisponible,
        int $saldoResultante
    ): string {
        return hash('sha256', serialize([
            'datos' => $datos,
            'movimiento_id' => $idEditando,
            'saldo_disponible' => $saldoDisponible,
            'saldo_resultante' => $saldoResultante,
        ]));
    }

    /**
     * Cancelar la edición y restablecer el formulario.
     */
    public function cancelarEdicion(): void
    {
        $this->reset([
            'movimientoEditandoId',
            'cuenta_id',
            'cuenta_destino_id',
            'categoria_id',
            'concepto',
            'valor',
            'medio_pago',
            'observacion',
        ]);

        $this->tipo = 'gasto';
        $this->fecha = now()->format('Y-m-d');

        $this->resetValidation();
        $this->confirmarGastoSinSaldo = false;
        $this->saldoDisponibleGasto = 0;
        $this->saldoResultanteGasto = 0;
        $this->limpiarConfirmacionGasto();
    }

    public function render()
    {
        return view('livewire.movimientos.movimiento-index', [
            'cuentas' => Cuenta::query()
                ->where('activa', true)
                ->orderBy('nombre')
                ->get(),

            'movimientos' => Movimiento::query()
                ->with([
                    'cuenta',
                    'cuentaDestino',
                    'abonoDeuda',
                    'movimientoAhorro',
                    'movimientoRecurrente',
                ])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->limit(20)
                ->get(),

            'categorias' => Categoria::query()
                ->where('activa', true)
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
