from typing import Protocol

from app.dominio import Desgaste, Lecturas, Ventana


class EstimadorDesgaste(Protocol):
    version: str

    def estimar(self, ventana: Ventana) -> Desgaste: ...


class FuenteLecturas(Protocol):
    def siguiente(self) -> Lecturas: ...
