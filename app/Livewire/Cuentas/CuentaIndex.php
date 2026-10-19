<?php

namespace App\Livewire\Cuentas;

use App\Models\Cuenta;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Cuentas')]
class CuentaIndex extends Component
{
    public ?int $cuentaId = null;

    public string $nombre = '';

    public string $tipo = 'efectivo';

    public int $saldo_inicial = 0;

    public ?string $fecha_apertura = null;

    public string $observacion = '';

    public function guardar(): void
    {
        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => [
                'required',
                'in:efectivo,banco,billetera_digital,ahorro,otro',
            ],
            'saldo_inicial' => ['required', 'integer', 'min:0'],
            'fecha_apertura' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        Cuenta::create([
            ...$datos,
            'activa' => true,
        ]);

        $this->limpiarFormulario();

        session()->flash('mensaje', 'Cuenta registrada correctamente.');
    }

    public function editar(int $id): void
    {
        $cuenta = Cuenta::findOrFail($id);

        $this->cuentaId = $cuenta->id;
        $this->nombre = $cuenta->nombre;
        $this->tipo = $cuenta->tipo;
        $this->fecha_apertura = $cuenta->fecha_apertura?->format('Y-m-d');
        $this->observacion = $cuenta->observacion ?? '';

        // El saldo inicial no se modifica durante la edición.
        $this->saldo_inicial = $cuenta->saldo_inicial;

        $this->resetValidation();
    }

    public function actualizar(): void
    {
        if (!$this->cuentaId) {
            return;
        }

        $datos = $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => [
                'required',
                'in:efectivo,banco,billetera_digital,ahorro,otro',
            ],
            'fecha_apertura' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $cuenta = Cuenta::findOrFail($this->cuentaId);

        $cuenta->update($datos);

        $this->limpiarFormulario();

        session()->flash('mensaje', 'Cuenta actualizada correctamente.');
    }

    public function cambiarEstado(int $id): void
    {
        $cuenta = Cuenta::findOrFail($id);

        $cuenta->update([
            'activa' => !$cuenta->activa,
        ]);

        session()->flash(
            'mensaje',
            $cuenta->activa
                ? 'Cuenta activada correctamente.'
                : 'Cuenta desactivada correctamente.'
        );
    }

    public function cancelarEdicion(): void
    {
        $this->limpiarFormulario();
    }

    private function limpiarFormulario(): void
    {
        $this->reset([
            'cuentaId',
            'nombre',
            'saldo_inicial',
            'fecha_apertura',
            'observacion',
        ]);

        $this->tipo = 'efectivo';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cuentas.cuenta-index', [
            'cuentas' => Cuenta::query()
                ->orderBy('nombre')
                ->get(),
        ]);
    }
}
