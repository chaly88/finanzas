<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // =========================
            // INGRESOS PERSONALES Y FAMILIARES
            // =========================
            [
                'nombre' => 'Salario',
                'tipo' => 'ingreso',
                'icono' => 'bi-cash-stack',
                'color' => '#198754',
            ],
            [
                'nombre' => 'Honorarios',
                'tipo' => 'ingreso',
                'icono' => 'bi-briefcase',
                'color' => '#0d6efd',
            ],
            [
                'nombre' => 'Bonificaciones',
                'tipo' => 'ingreso',
                'icono' => 'bi-gift',
                'color' => '#6f42c1',
            ],
            [
                'nombre' => 'Pensiones y subsidios',
                'tipo' => 'ingreso',
                'icono' => 'bi-person-check',
                'color' => '#20c997',
            ],
            [
                'nombre' => 'Arrendamientos',
                'tipo' => 'ingreso',
                'icono' => 'bi-house-check',
                'color' => '#fd7e14',
            ],
            [
                'nombre' => 'Intereses y rendimientos',
                'tipo' => 'ingreso',
                'icono' => 'bi-graph-up-arrow',
                'color' => '#0dcaf0',
            ],
            [
                'nombre' => 'Otros ingresos',
                'tipo' => 'ingreso',
                'icono' => 'bi-plus-circle',
                'color' => '#6f42c1',
            ],

            // =========================
            // GASTOS PERSONALES Y FAMILIARES
            // =========================
            [
                'nombre' => 'Alimentación',
                'tipo' => 'gasto',
                'icono' => 'bi-basket',
                'color' => '#fd7e14',
            ],
            [
                'nombre' => 'Vivienda',
                'tipo' => 'gasto',
                'icono' => 'bi-house',
                'color' => '#dc3545',
            ],
            [
                'nombre' => 'Servicios públicos',
                'tipo' => 'gasto',
                'icono' => 'bi-lightning',
                'color' => '#ffc107',
            ],
            [
                'nombre' => 'Telefonía e internet',
                'tipo' => 'gasto',
                'icono' => 'bi-wifi',
                'color' => '#0d6efd',
            ],
            [
                'nombre' => 'Mantenimiento del hogar',
                'tipo' => 'gasto',
                'icono' => 'bi-tools',
                'color' => '#795548',
            ],
            [
                'nombre' => 'Salud',
                'tipo' => 'gasto',
                'icono' => 'bi-heart-pulse',
                'color' => '#d63384',
            ],
            [
                'nombre' => 'Educación',
                'tipo' => 'gasto',
                'icono' => 'bi-mortarboard',
                'color' => '#6610f2',
            ],
            [
                'nombre' => 'Familia y niños',
                'tipo' => 'gasto',
                'icono' => 'bi-people',
                'color' => '#e83e8c',
            ],
            [
                'nombre' => 'Mascotas',
                'tipo' => 'gasto',
                'icono' => 'bi-heart',
                'color' => '#8b5e3c',
            ],
            [
                'nombre' => 'Cuidado personal',
                'tipo' => 'gasto',
                'icono' => 'bi-person',
                'color' => '#20c997',
            ],
            [
                'nombre' => 'Ropa y calzado',
                'tipo' => 'gasto',
                'icono' => 'bi-bag',
                'color' => '#6f42c1',
            ],
            [
                'nombre' => 'Transporte',
                'tipo' => 'gasto',
                'icono' => 'bi-car-front',
                'color' => '#0dcaf0',
            ],
            [
                'nombre' => 'Seguros',
                'tipo' => 'gasto',
                'icono' => 'bi-shield-check',
                'color' => '#198754',
            ],
            [
                'nombre' => 'Impuestos y trámites',
                'tipo' => 'gasto',
                'icono' => 'bi-file-earmark-text',
                'color' => '#6c757d',
            ],
            [
                'nombre' => 'Deudas',
                'tipo' => 'gasto',
                'icono' => 'bi-credit-card',
                'color' => '#b02a37',
            ],
            [
                'nombre' => 'Comisiones bancarias',
                'tipo' => 'gasto',
                'icono' => 'bi-bank',
                'color' => '#495057',
            ],
            [
                'nombre' => 'Entretenimiento',
                'tipo' => 'gasto',
                'icono' => 'bi-controller',
                'color' => '#6c757d',
            ],
            [
                'nombre' => 'Suscripciones',
                'tipo' => 'gasto',
                'icono' => 'bi-calendar-check',
                'color' => '#6610f2',
            ],
            [
                'nombre' => 'Viajes',
                'tipo' => 'gasto',
                'icono' => 'bi-airplane',
                'color' => '#0d6efd',
            ],
            [
                'nombre' => 'Regalos y donaciones',
                'tipo' => 'gasto',
                'icono' => 'bi-gift',
                'color' => '#d63384',
            ],
            [
                'nombre' => 'Compras personales',
                'tipo' => 'gasto',
                'icono' => 'bi-bag',
                'color' => '#795548',
            ],
            [
                'nombre' => 'Otros gastos',
                'tipo' => 'gasto',
                'icono' => 'bi-three-dots',
                'color' => '#343a40',
            ],
        ];

        foreach ($categorias as $datos) {
            Categoria::firstOrCreate(
                [
                    'nombre' => $datos['nombre'],
                    'tipo' => $datos['tipo'],
                ],
                [
                    'icono' => $datos['icono'],
                    'color' => $datos['color'],
                    'activa' => true,
                ]
            );
        }
    }
}
