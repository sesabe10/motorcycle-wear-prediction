from pydantic import BaseModel, Field

from app.dominio import Desgaste


class VentanaEntrada(BaseModel):
    h_desde_cambio_aceite: float = Field(ge=0, description="Horas de motor desde el último cambio de aceite")
    arranques_frio_desde_cambio: int = Field(ge=0)
    temp_aceite_media_desde_cambio: float = Field(ge=-50, le=400, description="°C")
    min_sobre_umbral_aceite: float = Field(ge=0, description="Minutos con el aceite sobre 115 °C")
    temp_aceite_media: float = Field(ge=-50, le=400)
    temp_aceite_max: float = Field(ge=-50, le=400)
    temp_aceite_p95: float = Field(ge=-50, le=400)
    temp_cabeza_media: float = Field(ge=-50, le=1400)
    delta_cabeza_aceite: float
    vib_rms_rel: float = Field(ge=0, description="Vibración RMS sobre la línea base de la moto")
    arranque_frio: int = Field(ge=0, le=1)
    h_desde_cambio_pastillas: float = Field(ge=0)
    decel_acum_pastillas: float = Field(ge=0, description="Suma de desaceleraciones (g) desde el cambio")
    n_bruscas_acum_pastillas: int = Field(default=0, ge=0)
    n_frenadas_acum_pastillas: int = Field(default=0, ge=0)


class AcumuladosEntrada(BaseModel):
    """Lo que devolvió la simulación anterior. Tras un cambio, ese componente va en cero."""
    h_desde_cambio_aceite: float = Field(default=0, ge=0)
    arranques_frio_desde_cambio: int = Field(default=0, ge=0)
    temp_aceite_media_desde_cambio: float = Field(default=0, ge=-50, le=400)
    min_sobre_umbral_aceite: float = Field(default=0, ge=0)
    h_desde_cambio_pastillas: float = Field(default=0, ge=0)
    decel_acum_pastillas: float = Field(default=0, ge=0)
    n_bruscas_acum_pastillas: int = Field(default=0, ge=0)
    n_frenadas_acum_pastillas: int = Field(default=0, ge=0)


class PrediccionSalida(BaseModel):
    desgaste_aceite_pct: float
    desgaste_pastillas_pct: float
    nivel_aceite: int = Field(description="0 normal, 1 precaución, 2 crítico")
    nivel_pastillas: int
    nivel_alerta: int = Field(description="El peor de los dos niveles")
    modelo_version: str

    @classmethod
    def desde(cls, desgaste: Desgaste, version: str) -> "PrediccionSalida":
        return cls(
            desgaste_aceite_pct=round(desgaste.aceite_pct, 2),
            desgaste_pastillas_pct=round(desgaste.pastillas_pct, 2),
            nivel_aceite=desgaste.nivel_aceite,
            nivel_pastillas=desgaste.nivel_pastillas,
            nivel_alerta=desgaste.nivel_alerta,
            modelo_version=version,
        )


class LecturasSalida(BaseModel):
    """Lo que midió el nodo en la ventana. Es lo que se guarda en la tabla ventanas."""
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


class SimulacionSalida(BaseModel):
    lecturas: LecturasSalida
    ventana: VentanaEntrada
    acumulados: AcumuladosEntrada = Field(description="Guárdalos y envíalos en la próxima simulación")
    prediccion: PrediccionSalida
