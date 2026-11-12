import os
from functools import lru_cache
from pathlib import Path

from fastapi import Header, HTTPException

from app.casos_de_uso import PredecirDesgaste, SimularVentana
from app.infraestructura.estimador_sklearn import EstimadorSklearn
from app.infraestructura.simulador import SimuladorNodo

CARPETA_MODELOS = Path(os.getenv("CARPETA_MODELOS", Path(__file__).resolve().parents[2] / "modelos"))
API_KEY = os.getenv("API_KEY", "")


@lru_cache
def estimador() -> EstimadorSklearn:
    return EstimadorSklearn(CARPETA_MODELOS)


@lru_cache
def simulador() -> SimuladorNodo:
    return SimuladorNodo(CARPETA_MODELOS / "lecturas_simulador.csv")


def predecir_desgaste() -> PredecirDesgaste:
    return PredecirDesgaste(estimador())


def simular_ventana() -> SimularVentana:
    return SimularVentana(simulador(), estimador())


def exigir_api_key(x_api_key: str = Header(default="")) -> None:
    if API_KEY and x_api_key != API_KEY:
        raise HTTPException(status_code=401, detail="API key incorrecta")
