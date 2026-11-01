@extends('adminlte::page')

@section('title', 'Finanzas Personales')


@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="mb-1">@yield('page_title', 'Finanzas Personales')</h1>
            <p class="text-muted mb-0">
                @yield('page_description', 'Administra tus finanzas personales.')
            </p>
        </div>
    </div>
@stop


@section('content')
    {{ $slot ?? '' }}
@stop

@section('footer')
    <div class="float-end d-none d-sm-inline">
        Control financiero personal
    </div>

    <strong>
        Finanzas Personales
    </strong>
    &copy; {{ date('Y') }}
@stop
