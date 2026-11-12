@extends('layouts.motocheck')

@section('title', 'Registrar cambio · MotoCheck')

@section('content')
@php
    $componenteElegido = old('componente', request('componente', 'aceite'));
@endphp

<section class="encabezado">
    <div class="encabezado__texto">
        <span class="etiqueta">{{ $moto['nombre'] }} · {{ $moto['placa'] }}</span>
        <h1 class="titulo">Registrar un cambio</h1>
        <p class="subtitulo">Al guardarlo, el desgaste de ese componente vuelve a cero y sus alertas abiertas se cierran.</p>
    </div>
</section>

<form class="formulario formulario--angosto" method="POST" action="{{ route('mantenimientos.store', $moto['id']) }}" novalidate>
    @csrf

    <fieldset>
        <legend>¿Qué cambiaste?</legend>
        <div class="opciones">
            <label class="opcion">
                <input type="radio" name="componente" value="aceite" @checked($componenteElegido === 'aceite')>
                <span>Aceite del motor</span>
            </label>
            <label class="opcion">
                <input type="radio" name="componente" value="pastillas" @checked($componenteElegido === 'pastillas')>
                <span>Pastillas de freno</span>
            </label>
        </div>
        @error('componente')<span class="error">{{ $message }}</span>@enderror
    </fieldset>

    <div class="campos">
        <div class="campo">
            <label for="fecha">Fecha del cambio</label>
            <input id="fecha" name="fecha" type="date" value="{{ old('fecha', now()->toDateString()) }}"
                   max="{{ now()->toDateString() }}" required @error('fecha') aria-invalid="true" @enderror>
            @error('fecha')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="campo">
            <label for="km">Kilometraje <small>(opcional)</small></label>
            <input id="km" name="km" type="number" min="0" inputmode="numeric" value="{{ old('km') }}">
            @error('km')<span class="error">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="campo">
        <label for="notas">Notas <small>(opcional)</small></label>
        <textarea id="notas" name="notas" rows="3" maxlength="255" placeholder="Taller, marca del aceite o de las pastillas">{{ old('notas') }}</textarea>
    </div>

    <div class="acciones">
        <button type="submit" class="btn btn--primario">Guardar cambio</button>
        <a href="{{ route('motos.panel', $moto['id']) }}" class="btn btn--texto">Cancelar</a>
    </div>
</form>
@endsection
