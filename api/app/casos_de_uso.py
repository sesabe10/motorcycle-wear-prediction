from dataclasses import dataclass

from app.dominio import Acumulados, Desgaste, Lecturas, Ventana
from app.puertos import EstimadorDesgaste, FuenteLecturas


class PredecirDesgaste:
    def __init__(self, estimador: EstimadorDesgaste):
        self._estimador = estimador

    def ejecutar(self, ventana: Ventana) -> Desgaste:
        return self._estimador.estimar(ventana)


@dataclass(frozen=True)
class ResultadoSimulacion:
    lecturas: Lecturas
    ventana: Ventana
    acumulados: Acumulados
    desgaste: Desgaste


class SimularVentana:
    """Genera la siguiente ventana de la moto y estima su desgaste."""

    def __init__(self, fuente: FuenteLecturas, estimador: EstimadorDesgaste):
        self._fuente = fuente
        self._estimador = estimador

    def ejecutar(self, acumulados: Acumulados) -> ResultadoSimulacion:
        lecturas = self._fuente.siguiente()
        nuevos_acumulados = acumulados.avanzar(lecturas)
        ventana = Ventana.desde(lecturas, nuevos_acumulados)
        return ResultadoSimulacion(lecturas, ventana, nuevos_acumulados, self._estimador.estimar(ventana))
