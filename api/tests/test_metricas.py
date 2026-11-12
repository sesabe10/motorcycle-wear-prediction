import pytest

from entrenamiento.entrenar import f1_de_alertas


def test_f1_perfecto_cuando_todos_los_niveles_coinciden():
    resultado = f1_de_alertas([10, 75, 95], [20, 72, 99])
    assert resultado["F1_macro"] == 1.0


def test_f1_baja_si_una_alerta_critica_se_predice_como_precaucion():
    resultado = f1_de_alertas([10, 75, 95, 96], [10, 75, 95, 80])
    assert resultado["F1_por_nivel"]["normal"] == 1.0
    assert resultado["F1_por_nivel"]["critico"] == pytest.approx(0.667, abs=1e-3)
    assert resultado["F1_macro"] < 1.0
