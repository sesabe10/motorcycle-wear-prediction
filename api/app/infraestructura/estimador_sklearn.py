import json
from dataclasses import asdict
from pathlib import Path

import joblib
import numpy as np
import pandas as pd

from app.dominio import Desgaste, Ventana
from app.infraestructura.variables import VARIABLES_ACEITE, VARIABLES_PASTILLAS, construir_variables


class EstimadorSklearn:
    """Carga los modelos entrenados y estima el desgaste de una ventana."""

    def __init__(self, carpeta_modelos: Path):
        self.info = json.loads((carpeta_modelos / "info.json").read_text(encoding="utf-8"))
        self.version = self.info["version"]
        self._modelo_aceite = joblib.load(carpeta_modelos / "aceite.joblib")
        self._modelo_pastillas = joblib.load(carpeta_modelos / "pastillas.joblib")

    def estimar(self, ventana: Ventana) -> Desgaste:
        datos = construir_variables(pd.DataFrame([asdict(ventana)]))
        return Desgaste(
            aceite_pct=self._predecir(self._modelo_aceite, datos[VARIABLES_ACEITE]),
            pastillas_pct=self._predecir(self._modelo_pastillas, datos[VARIABLES_PASTILLAS]),
        )

    @staticmethod
    def _predecir(modelo, variables: pd.DataFrame) -> float:
        return float(np.clip(modelo.predict(variables)[0], 0, 100))
