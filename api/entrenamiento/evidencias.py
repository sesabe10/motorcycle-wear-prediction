"""Genera las figuras y ejemplos para el informe a partir del conjunto de prueba.

Uso (después de entrenar): python -m entrenamiento.evidencias
Deja todo en la carpeta evidencias/.
"""
import json
from dataclasses import fields
from pathlib import Path

import joblib
import matplotlib

matplotlib.use("Agg")
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
from sklearn.inspection import permutation_importance
from sklearn.metrics import ConfusionMatrixDisplay

from app.dominio import UMBRAL_CRITICO, UMBRAL_PRECAUCION, Nivel, Ventana
from app.infraestructura.variables import VARIABLES_ACEITE, VARIABLES_PASTILLAS, construir_variables
from entrenamiento.entrenar import CARPETA_MODELOS, DATASET, SEMILLA, niveles

CARPETA = Path(__file__).resolve().parents[1] / "evidencias"
COMPONENTES = {
    "aceite": ("Aceite del motor", VARIABLES_ACEITE, "desgaste_aceite_pct", "#1B7F4C"),
    "pastillas": ("Pastillas de freno", VARIABLES_PASTILLAS, "desgaste_pastillas_pct", "#0B5C8A"),
}
NOMBRES_NIVEL = ["Normal", "Precaución", "Crítico"]

plt.rcParams.update({"font.size": 11, "axes.spines.top": False, "axes.spines.right": False})


def lineas_umbral(eje, horizontal=True):
    for umbral, color in ((UMBRAL_PRECAUCION, "#B76E00"), (UMBRAL_CRITICO, "#B42318")):
        (eje.axhline if horizontal else eje.axvline)(umbral, color=color, lw=1, ls="--")


def figura_real_vs_estimado(prueba, estimados):
    fig, ejes = plt.subplots(1, 2, figsize=(11, 5))
    for eje, (clave, (titulo, _, objetivo, color)) in zip(ejes, COMPONENTES.items()):
        eje.scatter(prueba[objetivo], estimados[clave], s=4, alpha=0.25, color=color)
        eje.plot([0, 100], [0, 100], color="#52636F", lw=1)
        lineas_umbral(eje)
        lineas_umbral(eje, horizontal=False)
        eje.set(title=titulo, xlabel="Desgaste real (%)", ylabel="Desgaste estimado (%)", xlim=(0, 100), ylim=(0, 100))
    fig.tight_layout()
    fig.savefig(CARPETA / "fig_real_vs_estimado.png", dpi=200)


def figura_matrices(prueba, estimados):
    fig, ejes = plt.subplots(1, 2, figsize=(11, 4.8))
    for eje, (clave, (titulo, _, objetivo, _)) in zip(ejes, COMPONENTES.items()):
        ConfusionMatrixDisplay.from_predictions(
            niveles(prueba[objetivo]), niveles(estimados[clave]), labels=list(Nivel),
            display_labels=NOMBRES_NIVEL, cmap="Blues", colorbar=False, ax=eje,
        )
        eje.set(title=titulo, xlabel="Nivel estimado", ylabel="Nivel real")
    fig.tight_layout()
    fig.savefig(CARPETA / "fig_matrices_confusion.png", dpi=200)


def figura_serie_moto(prueba, estimados):
    moto = prueba["moto_id"].iloc[0]
    filas = prueba["moto_id"] == moto
    datos = prueba[filas].assign(**{f"est_{c}": estimados[c][filas.to_numpy()] for c in COMPONENTES})
    datos = datos.sort_values("fecha_fin")
    horas = np.arange(len(datos)) * 0.25
    fig, ejes = plt.subplots(2, 1, figsize=(11, 6.5), sharex=True)
    for eje, (clave, (titulo, _, objetivo, color)) in zip(ejes, COMPONENTES.items()):
        eje.plot(horas, datos[objetivo], color="#52636F", lw=1.5, label="Real")
        eje.plot(horas, datos[f"est_{clave}"], color=color, lw=1.2, alpha=0.85, label="Estimado")
        lineas_umbral(eje)
        eje.set(title=f"{titulo}, moto de prueba {moto}", ylabel="Desgaste (%)", ylim=(0, 105))
        eje.legend(loc="upper left", frameon=False)
    ejes[-1].set_xlabel("Horas de uso simuladas")
    fig.tight_layout()
    fig.savefig(CARPETA / "fig_serie_moto_prueba.png", dpi=200)
    return moto


def figura_importancia(modelo, prueba):
    muestra = prueba.sample(min(3000, len(prueba)), random_state=SEMILLA)
    resultado = permutation_importance(
        modelo, muestra[VARIABLES_ACEITE], muestra["desgaste_aceite_pct"],
        scoring="neg_mean_absolute_error", n_repeats=5, random_state=SEMILLA,
    )
    orden = np.argsort(resultado.importances_mean)
    fig, eje = plt.subplots(figsize=(8, 5))
    eje.barh(np.array(VARIABLES_ACEITE)[orden], resultado.importances_mean[orden], color="#1B7F4C")
    eje.set(title="Importancia por permutación en el modelo de aceite",
            xlabel="Aumento del MAE al desordenar la variable (puntos %)")
    fig.tight_layout()
    fig.savefig(CARPETA / "fig_importancia_aceite.png", dpi=200)
    return dict(zip(VARIABLES_ACEITE, np.round(resultado.importances_mean, 2).tolist()))


def ejemplos_para_docs(prueba, estimados):
    """Una ventana de prueba por nivel de aceite, lista para pegar en /predict."""
    campos = [campo.name for campo in fields(Ventana)]
    nivel_real = np.array(niveles(prueba["desgaste_aceite_pct"]))
    ejemplos = []
    for nivel in Nivel:
        posiciones = np.flatnonzero(nivel_real == nivel)
        if len(posiciones) == 0:
            continue
        i = posiciones[len(posiciones) // 2]
        fila = prueba.iloc[i]
        ejemplos.append({
            "ventana_id": str(fila["ventana_id"]),
            "moto_id": str(fila["moto_id"]),
            "nivel_real_aceite": NOMBRES_NIVEL[nivel],
            "desgaste_real": {"aceite": round(float(fila["desgaste_aceite_pct"]), 2),
                              "pastillas": round(float(fila["desgaste_pastillas_pct"]), 2)},
            "desgaste_estimado_offline": {"aceite": round(float(estimados["aceite"][i]), 2),
                                          "pastillas": round(float(estimados["pastillas"][i]), 2)},
            "cuerpo_para_predict": {c: (int(fila[c]) if c in ("arranque_frio", "arranques_frio_desde_cambio",
                                                              "n_bruscas_acum_pastillas", "n_frenadas_acum_pastillas")
                                        else round(float(fila[c]), 4)) for c in campos},
        })
    (CARPETA / "ejemplos_prueba.json").write_text(json.dumps(ejemplos, indent=2, ensure_ascii=False), encoding="utf-8")


def main() -> None:
    CARPETA.mkdir(exist_ok=True)
    datos = construir_variables(pd.read_csv(DATASET))
    prueba = datos[datos["split"] == "test"].reset_index(drop=True)
    modelos = {c: joblib.load(CARPETA_MODELOS / f"{c}.joblib") for c in COMPONENTES}
    estimados = {c: np.clip(modelos[c].predict(prueba[v]), 0, 100) for c, (_, v, _, _) in COMPONENTES.items()}

    figura_real_vs_estimado(prueba, estimados)
    figura_matrices(prueba, estimados)
    moto = figura_serie_moto(prueba, estimados)
    importancia = figura_importancia(modelos["aceite"], prueba)
    ejemplos_para_docs(prueba, estimados)

    resumen = {
        "ventanas_por_split": datos["split"].value_counts().to_dict(),
        "motos_por_split": datos.groupby("split")["moto_id"].nunique().to_dict(),
        "ventanas_prueba_por_nivel": {
            c: dict(zip(NOMBRES_NIVEL, np.bincount(niveles(prueba[o]), minlength=3).tolist()))
            for c, (_, _, o, _) in COMPONENTES.items()
        },
        "moto_de_la_serie": str(moto),
        "importancia_aceite_MAE": importancia,
    }
    (CARPETA / "resumen.json").write_text(json.dumps(resumen, indent=2, ensure_ascii=False), encoding="utf-8")
    print(json.dumps(resumen, indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()
