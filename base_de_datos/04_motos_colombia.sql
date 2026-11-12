-- Marcas y modelos de motos populares en Colombia.
-- Ejecutar en Supabase > SQL Editor después de 01_esquema.sql.
-- Se puede correr varias veces: lo que ya existe no se duplica.

INSERT INTO marcas (nombre) VALUES
  ('AKT'), ('Bajaj'), ('Hero'), ('Honda'), ('KTM'),
  ('Suzuki'), ('TVS'), ('Victory'), ('Yamaha')
ON CONFLICT (nombre) DO NOTHING;

INSERT INTO modelos_moto (marca_id, nombre, cilindrada_cc)
SELECT ma.id, v.modelo, v.cc
FROM (VALUES
  ('AKT',     'NKD 125',            125),
  ('AKT',     'CR4 125',            125),
  ('AKT',     'TT 125 DS',          125),
  ('AKT',     'Special 110',        110),
  ('Bajaj',   'Boxer CT100',        100),
  ('Bajaj',   'Discover 125',       125),
  ('Bajaj',   'Pulsar NS 125',      125),
  ('Bajaj',   'Pulsar NS 160',      160),
  ('Bajaj',   'Pulsar N160',        160),
  ('Bajaj',   'Pulsar NS 200',      200),
  ('Bajaj',   'Dominar 400',        373),
  ('Hero',    'Eco Deluxe',         100),
  ('Hero',    'Hunk 125R',          125),
  ('Hero',    'Ignitor 125',        125),
  ('Honda',   'Wave 110S',          110),
  ('Honda',   'Navi 110',           110),
  ('Honda',   'Dio 110',            110),
  ('Honda',   'CB 125F',            125),
  ('Honda',   'XR 150L',            150),
  ('Honda',   'CB 160F',            160),
  ('Honda',   'CB 190R',            190),
  ('Honda',   'XR 190L',            190),
  ('KTM',     'Duke 200',           200),
  ('Suzuki',  'Viva R 115',         115),
  ('Suzuki',  'GN 125',             125),
  ('Suzuki',  'DR 150',             150),
  ('Suzuki',  'Gixxer 150',         150),
  ('TVS',     'Sport 100',          100),
  ('TVS',     'Raider 125',         125),
  ('TVS',     'NTorq 125',          125),
  ('TVS',     'Apache RTR 160 4V',  160),
  ('Victory', 'Advance R 125',      125),
  ('Victory', 'MRX 125',            125),
  ('Yamaha',  'Crypton FI',         115),
  ('Yamaha',  'YBR 125',            125),
  ('Yamaha',  'XTZ 125',            125),
  ('Yamaha',  'XTZ 150',            150),
  ('Yamaha',  'FZ 2.0',             150),
  ('Yamaha',  'NMAX 155',           155),
  ('Yamaha',  'MT-15',              155),
  ('Yamaha',  'FZ 25',              249)
) AS v (marca, modelo, cc)
JOIN marcas ma ON ma.nombre = v.marca
ON CONFLICT (marca_id, nombre) DO UPDATE
  SET cilindrada_cc = COALESCE(modelos_moto.cilindrada_cc, EXCLUDED.cilindrada_cc);
