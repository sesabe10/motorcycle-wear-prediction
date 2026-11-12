from dataclasses import fields
from pathlib import Path

import numpy as np
import pandas as pd

from app.dominio import Lecturas

COLUMNAS_LECTURAS = [campo.name for campo in fields(Lecturas)]


class SimuladorNodo:
    """Nodo IoT simulado (SIL): entrega lecturas de ventanas reales del dataset híbrido."""

    def __init__(self, archivo_lecturas: Path, semilla: int | None = None):
        self._lecturas = pd.read_csv(archivo_lecturas)[COLUMNAS_LECTURAS]
        self._azar = np.random.default_rng(semilla)

    def siguiente(self) -> Lecturas:
        fila = self._lecturas.iloc[self._azar.integers(len(self._lecturas))]
        return Lecturas(**{campo.name: campo.type(fila[campo.name]) for campo in fields(Lecturas)})
