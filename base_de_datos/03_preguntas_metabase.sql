-- Preguntas SQL para armar los dashboards en Metabase (New > SQL query).
-- {{usuario}} es una variable de Metabase: créala de tipo Number.
-- Laravel la envía firmada en el token, así que cada propietario solo ve sus motos.

-- 1) Desgaste en el tiempo (gráfico de líneas: inicio en X, desgaste_pct en Y, serie por componente)
SELECT vp.inicio, vp.componente, vp.desgaste_pct, mo.placa
FROM v_predicciones vp
JOIN motos mo ON mo.id = vp.moto_id
WHERE mo.user_id = {{usuario}}
ORDER BY vp.inicio;

-- 2) Alertas por estado (barras)
SELECT va.componente, va.nivel, va.estado, COUNT(*) AS alertas
FROM v_alertas va
JOIN motos mo ON mo.id = va.moto_id
WHERE mo.user_id = {{usuario}}
GROUP BY va.componente, va.nivel, va.estado;

-- 3) Mantenimientos registrados (tabla)
SELECT mo.placa, c.nombre AS componente, ma.fecha, ma.km
FROM mantenimientos ma
JOIN motos mo ON mo.id = ma.moto_id
JOIN componentes c ON c.id = ma.componente_id
WHERE mo.user_id = {{usuario}}
ORDER BY ma.fecha DESC;