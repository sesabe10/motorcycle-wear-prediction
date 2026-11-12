from dataclasses import dataclass
from enum import IntEnum

HORAS_POR_VENTANA = 0.25
UMBRAL_PRECAUCION = 70.0
UMBRAL_CRITICO = 90.0


class Nivel(IntEnum):
    NORMAL = 0
    PRECAUCION = 1
    CRITICO = 2


def nivel_de(desgaste_pct: float) -> Nivel:
    if desgaste_pct > UMBRAL_CRITICO:
        return Nivel.CRITICO
    if desgaste_pct >= UMBRAL_PRECAUCION:
        return Nivel.PRECAUCION
    return Nivel.NORMAL


@dataclass(frozen=True)
class Lecturas:
    """Lo que mide el nodo de la moto durante una ventana de 15 minutos."""
    temp_aceite_media: float
    temp_aceite_max: float
    temp_aceite_p95: float
    temp_cabeza_media: float
    delta_cabeza_aceite: float
    vib_rms_rel: float
    arranque_frio: int
    n_frenadas: int
    n_frenadas_bruscas: int
    decel_sum_g: float
    min_sobre_umbral: float


@dataclass(frozen=True)
class Acumulados:
    """Uso acumulado desde el último cambio de cada componente."""
    h_desde_cambio_aceite: float = 0.0
    arranques_frio_desde_cambio: int = 0
    temp_aceite_media_desde_cambio: float = 0.0
    min_sobre_umbral_aceite: float = 0.0
    h_desde_cambio_pastillas: float = 0.0
    decel_acum_pastillas: float = 0.0
    n_bruscas_acum_pastillas: int = 0
    n_frenadas_acum_pastillas: int = 0

    def avanzar(self, lecturas: Lecturas) -> "Acumulados":
        sin_temperatura_previa = self.temp_aceite_media_desde_cambio == 0
        ventanas_previas = 0 if sin_temperatura_previa else self.h_desde_cambio_aceite / HORAS_POR_VENTANA
        temp_media = (
            self.temp_aceite_media_desde_cambio * ventanas_previas + lecturas.temp_aceite_media
        ) / (ventanas_previas + 1)
        return Acumulados(
            h_desde_cambio_aceite=self.h_desde_cambio_aceite + HORAS_POR_VENTANA,
            arranques_frio_desde_cambio=self.arranques_frio_desde_cambio + lecturas.arranque_frio,
            temp_aceite_media_desde_cambio=temp_media,
            min_sobre_umbral_aceite=self.min_sobre_umbral_aceite + lecturas.min_sobre_umbral,
            h_desde_cambio_pastillas=self.h_desde_cambio_pastillas + HORAS_POR_VENTANA,
            decel_acum_pastillas=self.decel_acum_pastillas + lecturas.decel_sum_g,
            n_bruscas_acum_pastillas=self.n_bruscas_acum_pastillas + lecturas.n_frenadas_bruscas,
            n_frenadas_acum_pastillas=self.n_frenadas_acum_pastillas + lecturas.n_frenadas,
        )


@dataclass(frozen=True)
class Ventana:
    """Entrada de los modelos: lecturas de la ventana más los acumulados."""
    h_desde_cambio_aceite: float
    arranques_frio_desde_cambio: int
    temp_aceite_media_desde_cambio: float
    min_sobre_umbral_aceite: float
    temp_aceite_media: float
    temp_aceite_max: float
    temp_aceite_p95: float
    temp_cabeza_media: float
    delta_cabeza_aceite: float
    vib_rms_rel: float
    arranque_frio: int
    h_desde_cambio_pastillas: float
    decel_acum_pastillas: float
    n_bruscas_acum_pastillas: int = 0
    n_frenadas_acum_pastillas: int = 0

    @classmethod
    def desde(cls, lecturas: Lecturas, acumulados: Acumulados) -> "Ventana":
        return cls(
            h_desde_cambio_aceite=acumulados.h_desde_cambio_aceite,
            arranques_frio_desde_cambio=acumulados.arranques_frio_desde_cambio,
            temp_aceite_media_desde_cambio=acumulados.temp_aceite_media_desde_cambio,
            min_sobre_umbral_aceite=acumulados.min_sobre_umbral_aceite,
            temp_aceite_media=lecturas.temp_aceite_media,
            temp_aceite_max=lecturas.temp_aceite_max,
            temp_aceite_p95=lecturas.temp_aceite_p95,
            temp_cabeza_media=lecturas.temp_cabeza_media,
            delta_cabeza_aceite=lecturas.delta_cabeza_aceite,
            vib_rms_rel=lecturas.vib_rms_rel,
            arranque_frio=lecturas.arranque_frio,
            h_desde_cambio_pastillas=acumulados.h_desde_cambio_pastillas,
            decel_acum_pastillas=acumulados.decel_acum_pastillas,
            n_bruscas_acum_pastillas=acumulados.n_bruscas_acum_pastillas,
            n_frenadas_acum_pastillas=acumulados.n_frenadas_acum_pastillas,
        )


@dataclass(frozen=True)
class Desgaste:
    aceite_pct: float
    pastillas_pct: float

    @property
    def nivel_aceite(self) -> Nivel:
        return nivel_de(self.aceite_pct)

    @property
    def nivel_pastillas(self) -> Nivel:
        return nivel_de(self.pastillas_pct)

    @property
    def nivel_alerta(self) -> Nivel:
        return max(self.nivel_aceite, self.nivel_pastillas)
