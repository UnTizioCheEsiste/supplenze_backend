CREATE TABLE dbmuybgxgpjqvi.privilegio(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  nome VARCHAR(30) NOT NULL
);
CREATE TABLE dbmuybgxgpjqvi.utente(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  nome VARCHAR(30) NOT NULL, 
  cognome VARCHAR(30) NOT NULL, 
  email VARCHAR(60) NOT NULL, 
  privilegio INT NOT NULL, 
  telefono VARCHAR(10) NOT NULL, 
  PASSWORD VARCHAR(30) NOT NULL, 
  FOREIGN KEY(privilegio) REFERENCES privilegio(id)
);
CREATE TABLE dbmuybgxgpjqvi.giorno(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  nome VARCHAR(10) NOT NULL
);
CREATE TABLE dbmuybgxgpjqvi.tipo_disponibilita(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  nome VARCHAR(30) NOT NULL, 
  descrizione VARCHAR(300)
);
CREATE TABLE dbmuybgxgpjqvi.ora(
  id INT PRIMARY KEY, start_time TIME NOT NULL, 
  finish_time TIME NOT NULL
);
CREATE TABLE dbmuybgxgpjqvi.disponibilita(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  docente INT NOT NULL, 
  tipo_disponibilita INT NOT NULL, 
  giorno INT, 
  ora INT, 
  data_inizio DATETIME, 
  data_fine DATETIME, 
  FOREIGN KEY(docente) REFERENCES utente(id), 
  FOREIGN KEY(tipo_disponibilita) REFERENCES tipo_disponibilita(id), 
  FOREIGN KEY(giorno) REFERENCES giorno(id), 
  FOREIGN KEY(ora) REFERENCES ora(id)
);
CREATE TABLE dbmuybgxgpjqvi.motivazione(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  nome VARCHAR(50) NOT NULL, 
  descrizione VARCHAR(300)
);
CREATE TABLE dbmuybgxgpjqvi.assenza(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  docente INT NOT NULL, 
  motivazione INT NOT NULL, 
  certificato_medico VARCHAR(30), 
  data_inizio DATETIME NOT NULL, 
  data_fine DATETIME NOT NULL, 
  nota VARCHAR(200), 
  FOREIGN KEY(docente) REFERENCES utente(id), 
  FOREIGN KEY(motivazione) REFERENCES motivazione(id)
);
CREATE TABLE dbmuybgxgpjqvi.supplenza(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  assenza INT NOT NULL, 
  ora INT NOT NULL, 
  data_supplenza DATE NOT NULL, 
  supplente INT, 
  da_retribuire TINYINT(1) DEFAULT FALSE, 
  non_necessaria TINYINT(1) DEFAULT FALSE, 
  nota VARCHAR(300), 
  FOREIGN KEY(ora) REFERENCES ora(id), 
  FOREIGN KEY(assenza) REFERENCES assenza(id), 
  FOREIGN KEY(supplente) REFERENCES utente(id)
);
CREATE TABLE dbmuybgxgpjqvi.compresenza(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  id_teorico INT NOT NULL, 
  id_laboratorio INT NOT NULL, 
  ora INT NOT NULL, 
  giorno INT NOT NULL, 
  FOREIGN KEY(ora) REFERENCES ora(id), 
  FOREIGN KEY(giorno) REFERENCES giorno(id), 
  FOREIGN KEY(id_teorico) REFERENCES utente(id), 
  FOREIGN KEY(id_laboratorio) REFERENCES utente(id)
);
CREATE TABLE dbmuybgxgpjqvi.banca_ore(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  tipo_ora VARCHAR(15) NOT NULL, 
  docente INT NOT NULL, 
  numero_ore INT NOT NULL, 
  nota VARCHAR(300), 
  FOREIGN KEY(docente) REFERENCES utente(id)
);
CREATE TABLE dbmuybgxgpjqvi.reset(
  id INT AUTO_INCREMENT PRIMARY KEY, 
  user_id INT NOT NULL, 
  PASSWORD VARCHAR(128) NOT NULL, 
  requested DATETIME NOT NULL DEFAULT NOW(), 
  expires DATETIME NOT NULL DEFAULT DATE_ADD(NOW(), INTERVAL 20 MINUTE), 
  completed BOOLEAN NOT NULL DEFAULT(FALSE), 
  FOREIGN KEY(user_id) REFERENCES utente(id)
);
INSERT INTO dbmuybgxgpjqvi.privilegio(nome) 
VALUES 
  ("Vicepreside"), 
  ("Segreteria"), 
  ("Docente"), 
  ("Centralino");
INSERT INTO dbmuybgxgpjqvi.utente(
  nome, cognome, email, privilegio, telefono, 
  `password`
) 
VALUES 
  (
    "Giulio", "Chiozzi", "chiozzi.giulio@iisviolamarchesini.edu.it", 
    3, "1234567890", "chiozzi"
  ), 
  (
    "Admin", "Admin", "admin.vicepreside@gmail.com", 
    1, "1234567890", "admin"
  ), 
  (
    "Admin", "Admin", "admin.segreteria@gmail.com", 
    2, "1234567890", "admin"
  ), 
  (
    "Admin", "Admin", "admin.docente@gmail.com", 
    3, "1234567890", "admin"
  ), 
  (
    "Admin", "Admin", "admin.centralino@gmail.com", 
    4, "1234567890", "admin"
  ), 
  (
    "Francesco", "Pirra", "pirra.francesco@iisviolamarchesini.edu.it", 
    3, "1234567890", "pirra"
  ), 
  (
    "Valeria", "Baleanu", "baleanu.valeria@iisviolamarchesini.edu.it", 
    3, "1234567890", "baleanu"
  ), 
  (
    "Nicolo", "Ciancaglia", "ciancaglia.nicolo@iisviolamarchesini.edu.it", 
    3, "1234567890", "ciancaglia"
  );
INSERT INTO dbmuybgxgpjqvi.giorno(nome) 
VALUES 
  ("lunedi"), 
  ("martedi"), 
  ("mercoledi"), 
  ("giovedi"), 
  ("venerdi"), 
  ("sabato");
INSERT INTO dbmuybgxgpjqvi.ora(id, start_time, finish_time) 
VALUES 
  (1, "8:00", "9:30"), 
  (2, "9:30", "10:30"), 
  (3, "10:30", "11:30"), 
  (4, "11:30", "12:30"), 
  (5, "12:30", "13:30");
INSERT INTO dbmuybgxgpjqvi.motivazione(nome) 
VALUES 
  ("Salute"), 
  ("Matrimonio"), 
  ("Maternità"), 
  ("Corsi di aggiornamento"), 
  ("Motivi familiari"), 
  ("Lutto");
INSERT INTO dbmuybgxgpjqvi.tipo_disponibilita(nome) 
VALUES 
  ("compresenza"), 
  ("ora buca"), 
  ("temporanea"), 
  ("permanente");
INSERT INTO dbmuybgxgpjqvi.compresenza(
  id_teorico, id_laboratorio, ora, giorno
) 
VALUES 
  (7, 8, 1, 3), 
  (7, 1, 1, 2), 
  (6, 8, 2, 1);
INSERT INTO dbmuybgxgpjqvi.disponibilita(
  docente, tipo_disponibilita, giorno, 
  ora, data_inizio, data_fine
) 
VALUES 
  (
    8, 1, NULL, NULL, '2026-04-26 08:00:00', 
    '2026-04-20 09:30:00'
  ), 
  (
    8, 1, NULL, NULL, '2025-04-20 08:00:00', 
    '2025-04-20 09:30:00'
  ), 
  (7, 4, 6, 5, NULL, NULL), 
  (4, 4, 6, 5, NULL, NULL);
INSERT INTO dbmuybgxgpjqvi.assenza(
  docente, motivazione, certificato_medico, 
  data_inizio, data_fine, nota
) 
VALUES 
  (
    1, 1, "0123456789", "2024-04-20 8:00:00", 
    "2024-04-20 13:30:00", NULL
  ), 
  (
    1, 1, "0123456789", "2020-05-23 8:00:00", 
    "2020-05-20 13:30:00", NULL
  ), 
  (
    7, 4, NULL, "2028-04-20 9:30:00", "2028-04-20 10:30:00", 
    "la classe deve svolgere i compiti assegnati"
  ), 
  (
    6, 5, "0123456789", "2024-04-20 8:00:00", 
    "2026-04-20 13:30:00", NULL
  ), 
  (
    6, 5, "0123456789", "2028-04-20 9:30:00", 
    "2028-04-20 10:30:00", "la 5E deve svolgere le simulazioni invalsi di italiano"
  );
INSERT INTO dbmuybgxgpjqvi.supplenza(
  assenza, ora, data_supplenza, supplente, 
  da_retribuire, non_necessaria, nota
) 
VALUES 
  (
    1, 1, '2024-04-20', NULL, NULL, TRUE, 
    "la classe 5E entra alle 9:30"
  ), 
  (
    1, 2, '2024-04-20', 6, FALSE, FALSE, 
    "la 1A è stata trasferita in aula 30"
  ), 
  (
    1, 3, '2024-04-20', 8, FALSE, FALSE, 
    NULL
  ), 
  (
    1, 4, '2024-04-20', 6, TRUE, FALSE, NULL
  ), 
  (
    1, 5, '2024-04-20', NULL, NULL, TRUE, 
    "la classe 3F esce alle 12:30"
  ), 
  (
    3, 2, '2028-04-20', 1, FALSE, FALSE, 
    NULL
  );
INSERT INTO dbmuybgxgpjqvi.banca_ore(
  tipo_ora, docente, numero_ore, nota
) 
VALUES 
  (
    "da recuperare", 1, 2, "da recuperare possibilmente entro il 24 maggio"
  ), 
  (
    "straordinario", 7, 3, "lezioni al serale del 14 aprile 2023"
  ), 
  ("da recuperare", 8, 5, NULL);
INSERT INTO dbmuybgxgpjqvi.reset(
  user_id, `password`, requested, completed
) 
VALUES 
  (
    1, "ABCDE", "2024-04-20 8:00:00", 
    FALSE
  ), 
  (
    3, "admin", "2024-04-10 10:00:00", 
    TRUE
  ), 
  (
    6, "adminnn", "2024-04-26 7:52:00", 
    FALSE
  );