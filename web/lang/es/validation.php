<?php

return [
    'required' => 'Escribe :attribute.',
    'required_if' => 'Escribe :attribute.',
    'integer' => ':Attribute debe ser un número entero.',
    'between' => ['numeric' => ':Attribute debe estar entre :min y :max.'],
    'exists' => 'Elige una opción de la lista.',
    'email' => 'Escribe un correo válido.',
    'confirmed' => 'Las contraseñas no coinciden.',
    'min' => ['string' => ':Attribute debe tener al menos :min caracteres.'],
    'max' => ['string' => ':Attribute no puede tener más de :max caracteres.'],
    'unique' => ':Attribute ya está registrado.',
    'attributes' => [
        'email' => 'el correo',
        'password' => 'la contraseña',
        'nombres' => 'los nombres',
        'apellidos' => 'los apellidos',
        'modelo_moto_id' => 'la moto',
        'marca_nueva' => 'la marca',
        'modelo_nuevo' => 'el modelo',
        'cilindrada_nueva' => 'el cilindraje',
    ],
];
