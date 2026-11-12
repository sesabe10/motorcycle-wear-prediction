from dataclasses import asdict

from fastapi import Depends, FastAPI

from app.api.dependencias import estimador, exigir_api_key, predecir_desgaste, simular_ventana
from app.api.esquemas import (
    AcumuladosEntrada, LecturasSalida, PrediccionSalida, SimulacionSalida, VentanaEntrada,
)
from app.casos_de_uso import PredecirDesgaste, SimularVentana
from app.dominio import Acumulados, Ventana

app = FastAPI(title="API de desgaste de motocicletas", version="1.0")
protegido = [Depends(exigir_api_key)]


@app.get("/health")
def health() -> dict:
    return {"status": "ok"}


@app.get("/model/info", dependencies=protegido)
def model_info() -> dict:
    return estimador().info


@app.post("/predict", response_model=PrediccionSalida, dependencies=protegido)
def predict(entrada: VentanaEntrada, caso: PredecirDesgaste = Depends(predecir_desgaste)):
    desgaste = caso.ejecutar(Ventana(**entrada.model_dump()))
    return PrediccionSalida.desde(desgaste, estimador().version)


@app.post("/simulate", response_model=SimulacionSalida, dependencies=protegido)
def simulate(entrada: AcumuladosEntrada, caso: SimularVentana = Depends(simular_ventana)):
    resultado = caso.ejecutar(Acumulados(**entrada.model_dump()))
    return SimulacionSalida(
        lecturas=LecturasSalida(**asdict(resultado.lecturas)),
        ventana=VentanaEntrada(**asdict(resultado.ventana)),
        acumulados=AcumuladosEntrada(**asdict(resultado.acumulados)),
        prediccion=PrediccionSalida.desde(resultado.desgaste, estimador().version),
    )
