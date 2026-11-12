import pytest
from fastapi.testclient import TestClient

from app.api import dependencias
from app.api.main import app
from app.dominio import Acumulados, Lecturas, Nivel, nivel_de

cliente = TestClient(app)

VENTANA = {
    "h_desde_cambio_aceite": 15.25, "arranques_frio_desde_cambio": 17,
    "temp_aceite_media_desde_cambio": 87.342, "min_sobre_umbral_aceite": 0,
    "temp_aceite_media": 87.628, "temp_aceite_max": 91.705, "temp_aceite_p95": 90.816,
    "temp_cabeza_media": 139.428, "delta_cabeza_aceite": 51.8, "vib_rms_rel": 0.852,
    "arranque_frio": 0, "h_desde_cambio_pastillas": 135.25, "decel_acum_pastillas": 8278.422,
    "n_bruscas_acum_pastillas": 5250, "n_frenadas_acum_pastillas": 33474,
}


@pytest.mark.parametrize("desgaste, esperado", [(0, Nivel.NORMAL), (70, Nivel.PRECAUCION), (90, Nivel.PRECAUCION), (90.1, Nivel.CRITICO)])
def test_nivel_segun_umbrales(desgaste, esperado):
    assert nivel_de(desgaste) == esperado


def test_acumulados_avanzan_una_ventana():
    lecturas = Lecturas(80, 90, 88, 130, 50, 1.0, 1, 40, 5, 10.0, 2.0)
    nuevos = Acumulados(h_desde_cambio_aceite=0.25, temp_aceite_media_desde_cambio=100).avanzar(lecturas)
    assert nuevos.h_desde_cambio_aceite == 0.5
    assert nuevos.temp_aceite_media_desde_cambio == 90
    assert nuevos.arranques_frio_desde_cambio == 1
    assert nuevos.decel_acum_pastillas == 10.0


def test_health():
    assert cliente.get("/health").json() == {"status": "ok"}


def test_predict_devuelve_desgaste_y_niveles():
    respuesta = cliente.post("/predict", json=VENTANA)
    assert respuesta.status_code == 200
    datos = respuesta.json()
    assert datos["desgaste_aceite_pct"] == pytest.approx(21.61, abs=0.01)
    assert datos["desgaste_pastillas_pct"] == pytest.approx(34.66, abs=0.01)
    assert datos["nivel_alerta"] == 0


def test_predict_rechaza_valores_imposibles():
    assert cliente.post("/predict", json={**VENTANA, "h_desde_cambio_aceite": -1}).status_code == 422


def test_simulate_avanza_la_moto_una_ventana():
    respuesta = cliente.post("/simulate", json={"h_desde_cambio_aceite": 10, "h_desde_cambio_pastillas": 50})
    assert respuesta.status_code == 200
    datos = respuesta.json()
    assert datos["acumulados"]["h_desde_cambio_aceite"] == 10.25
    assert datos["ventana"]["h_desde_cambio_pastillas"] == 50.25
    assert 0 <= datos["prediccion"]["desgaste_aceite_pct"] <= 100
    assert "decel_sum_g" in datos["lecturas"]


def test_api_key_incorrecta_responde_401(monkeypatch):
    monkeypatch.setattr(dependencias, "API_KEY", "secreta")
    assert cliente.post("/predict", json=VENTANA, headers={"X-API-Key": "otra"}).status_code == 401
    assert cliente.post("/predict", json=VENTANA, headers={"X-API-Key": "secreta"}).status_code == 200
