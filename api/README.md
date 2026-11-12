# API de desgaste de motocicletas

Estima el desgaste del aceite y de las pastillas de freno de una moto y el nivel de alerta (0 normal, 1 precaución desde 70 %, 2 crítico sobre 90 %). Incluye el nodo IoT simulado (SIL), así que Laravel solo envía el estado de la moto.

## Estructura

```
app/
  dominio.py              reglas del negocio: ventana, acumulados, niveles de alerta
  puertos.py              interfaces que el dominio necesita (estimador, fuente de lecturas)
  casos_de_uso.py         PredecirDesgaste y SimularVentana
  infraestructura/        scikit-learn, simulador del nodo y cálculo de variables
  api/                    FastAPI: rutas, esquemas y dependencias
entrenamiento/entrenar.py entrena los modelos y genera la carpeta modelos/
entrenamiento/evidencias.py genera las figuras de evaluación en evidencias/
modelos/                  modelos entrenados (ya incluidos)
datos/                    dataset híbrido usado para entrenar
evidencias/               figuras, ejemplos de prueba y resumen de la evaluación
tests/                    pruebas de la API
```

Las dependencias apuntan hacia adentro: `api` e `infraestructura` conocen al dominio, el dominio no conoce a nadie.

## Instalar y ejecutar

Requiere Python 3.10 o superior.

```bash
python -m venv .venv
source .venv/bin/activate          # Windows: .venv\Scripts\activate
pip install -r requirements.txt
cp .env.example .env               # Windows: copy .env.example .env
python -m uvicorn app.api.main:app --port 8001 --env-file .env
```

Documentación interactiva: http://127.0.0.1:8001/docs

Para reentrenar (opcional, los modelos ya vienen entrenados):

```bash
python -m entrenamiento.entrenar
```

Resultado esperado en las 5 motos de prueba:

| | RMSE | MAE | F1 macro |
|---|---|---|---|
| Aceite | 7.17 | 5.00 | 0.722 |
| Pastillas | 9.63 | 7.51 | 0.724 |

Pruebas y figuras de evaluación (necesitan `pip install -r requirements-dev.txt`):

```bash
python -m pytest                  # 12 pruebas
python -m entrenamiento.evidencias
```

## Rutas

Todas, menos `/health`, exigen el encabezado `X-API-Key` si `API_KEY` tiene valor.

| Método | Ruta | Para qué |
|---|---|---|
| GET | `/health` | Saber si la API está arriba |
| GET | `/model/info` | Versión, variables y métricas de los modelos |
| POST | `/simulate` | Recibe los acumulados de la moto, simula una ventana de 15 min y devuelve ventana, acumulados nuevos y predicción |
| POST | `/predict` | Recibe una ventana ya calculada y devuelve la predicción |

Ejemplo de `/simulate`:

```json
{ "h_desde_cambio_aceite": 60, "temp_aceite_media_desde_cambio": 85,
  "h_desde_cambio_pastillas": 200, "decel_acum_pastillas": 12000 }
```

Los campos que no se envían valen 0. Para una moto recién registrada basta `{}`.

## Uso desde Laravel

El cliente para Laravel (`app/Services/DesgasteApi.php`) viene en el proyecto MotoCheck. Laravel envía la clave en el encabezado `X-API-Key`; debe ser igual a `API_KEY` del `.env` de esta API.

## Límite

Las etiquetas de desgaste del dataset son sintéticas: las métricas miden el ajuste al generador de datos, no a motos reales.
