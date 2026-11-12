"""Variables que usan los modelos. Las comparten el entrenamiento y la API."""
import pandas as pd

HORAS_MINIMAS = 0.25  # evita dividir por cero justo después de un cambio

VARIABLES_ACEITE = [
    "h_desde_cambio_aceite", "frio_por_h", "temp_aceite_media_desde_cambio",
    "min_sobre_umbral_aceite", "temp_aceite_media", "temp_aceite_max",
    "temp_aceite_p95", "temp_cabeza_media", "delta_cabeza_aceite",
    "vib_rms_rel", "arranque_frio",
]
VARIABLES_PASTILLAS = ["h_p", "h2_p", "h_x_decel", "h2_x_decel"]


def construir_variables(ventanas: pd.DataFrame) -> pd.DataFrame:
    datos = ventanas.copy()
    horas_aceite = datos["h_desde_cambio_aceite"].clip(lower=HORAS_MINIMAS)
    horas_pastillas = datos["h_desde_cambio_pastillas"].clip(lower=HORAS_MINIMAS)
    decel_por_hora = datos["decel_acum_pastillas"] / horas_pastillas

    datos["frio_por_h"] = datos["arranques_frio_desde_cambio"] / horas_aceite
    datos["h_p"] = horas_pastillas
    datos["h2_p"] = horas_pastillas ** 2
    datos["h_x_decel"] = horas_pastillas * decel_por_hora
    datos["h2_x_decel"] = horas_pastillas ** 2 * decel_por_hora
    return datos
