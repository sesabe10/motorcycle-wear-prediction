@extends('layouts.motocheck')

@section('title', 'Registrar moto · MotoCheck')

@section('content')

<section class="encabezado">
    <div class="encabezado__texto">
        <h1 class="titulo">Registra tu moto</h1>
        <p class="subtitulo">Solo pedimos lo necesario. El resto lo estima el sistema con los datos del nodo simulado.</p>
    </div>
</section>

<div class="registro">
    <form class="formulario" method="POST" action="{{ route('motos.store') }}" novalidate>
        @csrf

        <fieldset>
            <legend>Tu moto</legend>
            <div class="campos">
                <div class="campo">
                    <label for="placa">Placa</label>
                    <input id="placa" name="placa" type="text" value="{{ old('placa') }}" placeholder="ABC12D"
                           maxlength="10" required @error('placa') aria-invalid="true" @enderror>
                    @error('placa')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="campo">
                    <label for="modelo_moto_id">Marca y modelo</label>
                    <select id="modelo_moto_id" name="modelo_moto_id" required @error('modelo_moto_id') aria-invalid="true" @enderror>
                        <option value="">Elige tu moto</option>
                        @foreach ($modelos as $marca => $lista)
                            <optgroup label="{{ $marca }}">
                                @foreach ($lista as $modelo)
                                    <option value="{{ $modelo['id'] }}" @selected(old('modelo_moto_id') == $modelo['id'])>{{ $marca }} {{ $modelo['nombre'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                        <option value="otra" @selected(old('modelo_moto_id') === 'otra')>Mi moto no está en la lista</option>
                    </select>
                    @error('modelo_moto_id')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="campos campos--uno otra-moto" id="otra-moto" @if (old('modelo_moto_id') !== 'otra') hidden @endif>
                    <p class="nota">Escríbela tal como aparece en la tarjeta de propiedad. Quedará en la lista para los demás usuarios.</p>
                    <div class="campo">
                        <label for="marca_nueva">Marca</label>
                        <input id="marca_nueva" name="marca_nueva" type="text" list="lista-marcas" value="{{ old('marca_nueva') }}"
                               maxlength="50" placeholder="Kawasaki" @error('marca_nueva') aria-invalid="true" @enderror>
                        <datalist id="lista-marcas">
                            @foreach ($marcas as $nombreMarca)
                                <option value="{{ $nombreMarca }}">
                            @endforeach
                        </datalist>
                        @error('marca_nueva')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo">
                        <label for="modelo_nuevo">Modelo</label>
                        <input id="modelo_nuevo" name="modelo_nuevo" type="text" value="{{ old('modelo_nuevo') }}"
                               maxlength="50" placeholder="Z400" @error('modelo_nuevo') aria-invalid="true" @enderror>
                        @error('modelo_nuevo')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo">
                        <label for="cilindrada_nueva">Cilindraje en cc <small>(opcional)</small></label>
                        <input id="cilindrada_nueva" name="cilindrada_nueva" type="number" min="50" max="2000"
                               inputmode="numeric" value="{{ old('cilindrada_nueva') }}" placeholder="399">
                        @error('cilindrada_nueva')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="campo">
                    <label for="alias">Apodo <small>(opcional)</small></label>
                    <input id="alias" name="alias" type="text" value="{{ old('alias') }}" placeholder="La azul" maxlength="50">
                </div>

                <div class="campo">
                    <label for="anio_fabricacion">Año <small>(opcional)</small></label>
                    <input id="anio_fabricacion" name="anio_fabricacion" type="number" min="1980" max="2100"
                           inputmode="numeric" value="{{ old('anio_fabricacion') }}" placeholder="2022">
                    @error('anio_fabricacion')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="campo">
                    <label for="km_al_registrar">Kilometraje actual <small>(opcional)</small></label>
                    <input id="km_al_registrar" name="km_al_registrar" type="number" min="0"
                           inputmode="numeric" value="{{ old('km_al_registrar') }}" placeholder="18450">
                    @error('km_al_registrar')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Último cambio de aceite</legend>
            <div class="campos">
                <div class="campo">
                    <label for="aceite_fecha">Fecha</label>
                    <input id="aceite_fecha" name="aceite_fecha" type="date" value="{{ old('aceite_fecha') }}"
                           max="{{ now()->toDateString() }}" required @error('aceite_fecha') aria-invalid="true" @enderror>
                    @error('aceite_fecha')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="campo">
                    <label for="aceite_km">Kilometraje en ese cambio <small>(opcional)</small></label>
                    <input id="aceite_km" name="aceite_km" type="number" min="0" inputmode="numeric" value="{{ old('aceite_km') }}">
                    @error('aceite_km')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Último cambio de pastillas</legend>
            <div class="campos">
                <div class="campo">
                    <label for="pastillas_fecha">Fecha</label>
                    <input id="pastillas_fecha" name="pastillas_fecha" type="date" value="{{ old('pastillas_fecha') }}"
                           max="{{ now()->toDateString() }}" required @error('pastillas_fecha') aria-invalid="true" @enderror>
                    @error('pastillas_fecha')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="campo">
                    <label for="pastillas_km">Kilometraje en ese cambio <small>(opcional)</small></label>
                    <input id="pastillas_km" name="pastillas_km" type="number" min="0" inputmode="numeric" value="{{ old('pastillas_km') }}">
                    @error('pastillas_km')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </fieldset>

        <p class="nota">Con la fecha basta. Si además das el kilometraje, el cálculo del uso es más preciso.</p>

        <div class="acciones">
            <button type="submit" class="btn btn--primario">Registrar moto</button>
            <a href="{{ route('inicio') }}" class="btn btn--texto">Cancelar</a>
        </div>
    </form>

    <aside class="pasos">
        <h2>Qué pasa después</h2>
        <ol>
            <li>Se te asigna un nodo simulado que envía datos de temperatura, vibración y frenado.</li>
            <li>El modelo estima el desgaste del aceite y de las pastillas en cada ventana de 15 minutos.</li>
            <li>Tú solo ves el resultado: una alerta de precaución a 70 % y una crítica a 90 %.</li>
        </ol>
    </aside>
</div>

<script>
    (function () {
        var lista = document.getElementById('modelo_moto_id');
        var otra = document.getElementById('otra-moto');
        function mostrar() {
            var escribir = lista.value === 'otra';
            otra.hidden = !escribir;
            document.getElementById('marca_nueva').required = escribir;
            document.getElementById('modelo_nuevo').required = escribir;
            if (escribir && !otra.dataset.visto) { otra.dataset.visto = '1'; document.getElementById('marca_nueva').focus(); }
        }
        lista.addEventListener('change', mostrar);
        mostrar();
    })();
</script>
@endsection
