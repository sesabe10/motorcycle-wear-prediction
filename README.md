# MotoCheck: mantenimiento predictivo de motocicletas

Prototipo web que estima el desgaste del aceite y de las pastillas de freno de una motocicleta y genera alertas al 70 % y al 90 %. Los sensores se simulan por software (SIL) y los modelos se entrenaron con un conjunto de datos híbrido: perfiles de manejo reales y desgaste sintético.

Trabajo de grado de Ingeniería de Software, Institución Universitaria Pascual Bravo (Medellín, 2026).

> Los datos de desgaste son simulados. Las alertas no deben usarse para decidir el mantenimiento de una moto real.

## Contenido del repositorio

```
motocheck/
├── api/            API en Python (FastAPI): simulador del nodo y modelos de desgaste
├── web/            Aplicación web en Laravel 12
└── base_de_datos/  Scripts SQL con los que se creó la base en Supabase (referencia)
```

La base de datos ya está en Supabase y el proyecto ya está configurado. Solo hay que instalar las dependencias y ejecutar.

## Cómo funciona

1. El usuario registra su moto en la aplicación web y pulsa **Simular**.
2. Laravel lee en la base de datos el uso acumulado de la moto y llama a la API.
3. La API simula 15 minutos de lecturas de sensores y estima el desgaste de cada componente.
4. Laravel guarda el resultado, muestra el desgaste en el panel y crea una alerta si se pasa un umbral.

## Requisitos

- [Python](https://www.python.org/downloads/) 3.10 o superior
- [PHP](https://www.php.net/downloads) 8.2 o superior, con la extensión `pdo_pgsql` activada
- [Composer](https://getcomposer.org/download/)

## Instalación

Se hace una sola vez.

**API:**

```bash
cd api
python -m venv .venv
.venv\Scripts\activate            # En macOS o Linux: source .venv/bin/activate
pip install -r requirements.txt
```

**Aplicación web:**

```bash
cd web
composer install
```

## Ejecutar

Abre dos terminales.

**Terminal 1, la API:**

```bash
cd api
.venv\Scripts\activate
python -m uvicorn app.api.main:app --port 8001 --env-file .env
```

**Terminal 2, la aplicación web:**

```bash
cd web
php artisan serve
```

Abre http://127.0.0.1:8000, crea tu cuenta, registra una moto y pulsa **Simular**.

La documentación de la API queda en http://127.0.0.1:8001/docs.

## Pruebas

```bash
cd api
pip install -r requirements-dev.txt
python -m pytest
```

## Problemas frecuentes

| Mensaje | Solución |
|---|---|
| `uvicorn no se reconoce` | Activa el entorno virtual (`.venv\Scripts\activate`) o usa `python -m uvicorn`. |
| No hay conexión con la API de desgaste | La API no está corriendo. Arráncala en la terminal 1. |
| La API respondió con un error 401 | La terminal tiene una clave vieja guardada. Abre una terminal nueva y vuelve a arrancar la API. |
| `could not find driver` | Activa `extension=pdo_pgsql` en tu `php.ini` y reinicia la terminal. |
| El puerto 8001 ya está en uso | Hay otra API corriendo. Ciérrala o cierra las terminales abiertas y vuelve a intentar. |