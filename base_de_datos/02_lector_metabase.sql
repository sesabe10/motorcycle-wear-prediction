-- Opcional: usuario de solo lectura para Metabase. Ejecutar después de 01_esquema.sql.
-- Cambia la contraseña. La tabla users queda fuera porque tiene correos y contraseñas.

CREATE ROLE metabase_lector LOGIN PASSWORD 'cambia-esta-clave';
GRANT USAGE ON SCHEMA public TO metabase_lector;

DO $$
DECLARE t text;
BEGIN
  FOREACH t IN ARRAY ARRAY['roles', 'componentes', 'niveles_alerta', 'marcas', 'modelos_moto',
                           'motos', 'mantenimientos', 'ventanas', 'modelos_ml', 'predicciones', 'alertas']
  LOOP
    EXECUTE format('GRANT SELECT ON public.%I TO metabase_lector', t);
    EXECUTE format('CREATE POLICY metabase_lee ON public.%I FOR SELECT TO metabase_lector USING (true)', t);
  END LOOP;
END $$;

GRANT SELECT ON v_predicciones, v_alertas, v_acumulados TO metabase_lector;
