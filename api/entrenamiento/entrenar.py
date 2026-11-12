"""Entrena los modelos de desgaste y deja en modelos/ lo que necesita la API.

Uso: python -m entrenamiento.entrenar
"""
import json
from pathlib import Path

import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import HistGradientBoostingRegressor
from sklearn.linear_model import RidgeCV
from sklearn.metrics import f1_score, mean_absolute_error, root_mean_squared_error
from sklearn.pipeline import make_pipeline
from sklearn.preprocessing import StandardScaler

from app.dominio import Nivel, nivel_de
from app.infraestructura.simulador import COLUMNAS_LECTURAS
from app.infraestructura.variables import VARIABLES_ACEITE, VARIABLES_PASTILLAS, construir_variables

RAIZ = Path(__file__).resolve().parents[1]
DATASET = RAIZ / "datos" / "dataset_hibrido_ventanas.csv"
CARPETA_MODELOS = RAIZ / "modelos"
SEMILLA = 42
VERSION = "1.0"


def modelo_aceite():
    return HistGradientBoostingRegressor(
        learning_rate=0.05, max_depth=4, max_iter=300, min_samples_leaf=50, random_state=SEMILLA
    )


def modelo_pastillas():
    return make_pipeline(StandardScaler(), RidgeCV(alphas=np.logspace(-3, 3, 13)))


def niveles(desgastes) -> list[Nivel]:
    return [nivel_de(valor) for valor in desgastes]


def f1_de_alertas(real, estimado) -> dict:
    """F1 del nivel de alerta (normal, precaución, crítico) que resulta del porcentaje."""
    nivel_real, nivel_estimado = niveles(real), niveles(estimado)
    por_nivel = f1_score(nivel_real, nivel_estimado, labels=list(Nivel), average=None, zero_division=0)
    return {
        "F1_macro": round(float(f1_score(nivel_real, nivel_estimado, labels=list(Nivel), average="macro", zero_division=0)), 3),
        "F1_por_nivel": {nivel.name.lower(): round(float(valor), 3) for nivel, valor in zip(Nivel, por_nivel)},
    }


def evaluar(modelo, variables: pd.DataFrame, real: pd.Series) -> dict:
    estimado = np.clip(modelo.predict(variables), 0, 100)
    return {
        "RMSE": round(root_mean_squared_error(real, estimado), 2),
        "MAE": round(mean_absolute_error(real, estimado), 2),
        **f1_de_alertas(real, estimado),
    }


def lecturas_por_ventana(datos: pd.DataFrame) -> pd.DataFrame:
    """Lecturas de una sola ventana, que usa el simulador del nodo."""
    datos = datos.sort_values(["moto_id", "fecha_fin"])
    acumulado = datos["min_sobre_umbral_aceite"]
    incremento = datos.groupby("moto_id")["min_sobre_umbral_aceite"].diff()
    es_primera_tras_cambio = datos["h_desde_cambio_aceite"] <= 0.25
    datos["min_sobre_umbral"] = acumulado.where(es_primera_tras_cambio, incremento).clip(lower=0)
    return datos[COLUMNAS_LECTURAS].dropna()


def main() -> None:
    datos = construir_variables(pd.read_csv(DATASET))
    entrenamiento = datos[datos["split"].isin(["train", "val"])]
    prueba = datos[datos["split"] == "test"]

    aceite = modelo_aceite().fit(entrenamiento[VARIABLES_ACEITE], entrenamiento["desgaste_aceite_pct"])
    pastillas = modelo_pastillas().fit(entrenamiento[VARIABLES_PASTILLAS], entrenamiento["desgaste_pastillas_pct"])

    info = {
        "version": VERSION,
        "entrenado_con": "train + val (25 motos)",
        "evaluado_en": "test (5 motos)",
        "umbrales": {"precaucion": 70.0, "critico": 90.0},
        "nota": "Etiquetas sintéticas: las métricas miden el ajuste al generador, no a motos reales.",
        "aceite": {
            "modelo": "HistGradientBoostingRegressor",
            "variables": VARIABLES_ACEITE,
            "metricas_prueba": evaluar(aceite, prueba[VARIABLES_ACEITE], prueba["desgaste_aceite_pct"]),
        },
        "pastillas": {
            "modelo": "Ridge sobre horas y frenado",
            "variables": VARIABLES_PASTILLAS,
            "metricas_prueba": evaluar(pastillas, prueba[VARIABLES_PASTILLAS], prueba["desgaste_pastillas_pct"]),
        },
    }

    CARPETA_MODELOS.mkdir(exist_ok=True)
    joblib.dump(aceite, CARPETA_MODELOS / "aceite.joblib")
    joblib.dump(pastillas, CARPETA_MODELOS / "pastillas.joblib")
    (CARPETA_MODELOS / "info.json").write_text(json.dumps(info, indent=2, ensure_ascii=False), encoding="utf-8")
    lecturas_por_ventana(datos).sample(5000, random_state=SEMILLA).to_csv(
        CARPETA_MODELOS / "lecturas_simulador.csv", index=False
    )

    print("Aceite   ", info["aceite"]["metricas_prueba"])
    print("Pastillas", info["pastillas"]["metricas_prueba"])


if __name__ == "__main__":
    main()
