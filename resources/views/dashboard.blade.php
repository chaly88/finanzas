
@extends('layouts.app')

@section('page_title', 'Dashboard financiero')

@section('page_content')
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="small-box text-bg-primary">
                <div class="inner">
                    <h3>$0</h3>
                    <p>Dinero disponible</p>
                </div>
                <div class="small-box-icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="small-box text-bg-success">
                <div class="inner">
                    <h3>$0</h3>
                    <p>Ingresos del mes</p>
                </div>
                <div class="small-box-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="small-box text-bg-danger">
                <div class="inner">
                    <h3>$0</h3>
                    <p>Gastos del mes</p>
                </div>
                <div class="small-box-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="small-box text-bg-info">
                <div class="inner">
                    <h3>$0</h3>
                    <p>Ahorro del mes</p>
                </div>
                <div class="small-box-icon">
                    <i class="fas fa-piggy-bank"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen financiero</h3>
        </div>

        <div class="card-body">
            Todavía no tienes movimientos registrados.
            Cuando configuremos las cuentas y los movimientos,
            aquí aparecerán tus cifras reales.
        </div>
    </div>
@stop
