create database dbmuybgxgpjqvi;

create table dbmuybgxgpjqvi.privilegio (
	id int auto_increment primary key,
	nome nvarchar(30) not null
);
create table dbmuybgxgpjqvi.utente (
	id int auto_increment primary key,
	nome nvarchar(30) not null,
	cognome nvarchar(30) not null,
	email nvarchar(60) not null,
	privilegio int not null,
	telefono nvarchar(10) not null,
	password nvarchar(30) not null,
	foreign key (privilegio) references privilegio(id)
);
create table dbmuybgxgpjqvi.giorno (
	id int auto_increment primary key,
	nome nvarchar(10) not null
);
create table dbmuybgxgpjqvi.tipo_disponibilita (
	id int auto_increment primary key,
	nome nvarchar(30) not null,
	descrizione nvarchar(300)
);
create table dbmuybgxgpjqvi.ora(
	id int primary key,
	data_inizio time not null,
	data_fine time not null
);
create table dbmuybgxgpjqvi.disponibilita (
	id int auto_increment primary key,
	docente int not null,
	tipo_disponibilita int not null,
	giorno int,
	ora int,
	data_inizio datetime,
	data_fine datetime,
	foreign key (docente) references utente(id),
	foreign key (tipo_disponibilita) references tipo_disponibilita(id),
	foreign key (giorno) references giorno(id),
	foreign key (ora) references ora(id)
);
create table dbmuybgxgpjqvi.motivazione (
	id int auto_increment primary key,
	nome nvarchar(50) not null,
	descrizione nvarchar(300)
);
create table dbmuybgxgpjqvi.assenza (
	id int auto_increment primary key,
	docente int not null,
	motivazione int not null,
	certificato_medico nvarchar(30),
	data_inizio datetime not null,
	data_fine datetime not null,
	nota nvarchar(200),
	foreign key (docente) references utente(id),
	foreign key (motivazione) references motivazione(id)
);
create table dbmuybgxgpjqvi.supplenza (
	id int auto_increment primary key,
	assenza int not null,
	ora int not null,
	data_supplenza date not null,
	supplente int,
	da_retribuire bool default false,
	non_necessaria bool default false,
	nota nvarchar(300),
	foreign key (ora) references ora(id),
	foreign key (assenza) references assenza(id),
	foreign key (supplente) references utente(id)
);
create table dbmuybgxgpjqvi.compresenza(
	id int auto_increment primary key,
	id_teorico int not null,
	id_laboratorio int not null,
	ora int not null,
	giorno int not null,
	foreign key (ora) references ora(id),
	foreign key (giorno) references giorno(id),
	foreign key (id_teorico) references utente(id),
	foreign key (id_laboratorio) references utente(id)
);
create table dbmuybgxgpjqvi.banca_ore(
	id int auto_increment primary key,
	tipo_ora nvarchar(15) not null,
	docente int not null,
	numero_ore int not null,
	nota nvarchar(300),
	foreign key (docente) references utente(id)
);

create table dbmuybgxgpjqvi.reset (
	id int auto_increment primary key,
	id_utente int not null,
	password nvarchar(128) not null,
	data_richiesta datetime not null default now(),
	data_scadenza datetime default date_add(now(), interval 20 minute),
	completato boolean not null default(false),
	foreign key (id_utente) references utente(id)
);

insert into dbmuybgxgpjqvi.privilegio (nome)
values ("Vicepreside"), ("Segreteria"), ("Docente"), ("Centralino");


insert into dbmuybgxgpjqvi.utente (nome, cognome, email, privilegio, telefono, `password`)
values ("Giulio", "Chiozzi", "chiozzi.giulio@iisviolamarchesini.edu.it", 3, "1234567890", "chiozzi"),
("Admin", "Admin", "admin.vicepreside@gmail.com", 1, "1234567890", "admin"),
("Admin", "Admin", "admin.segreteria@gmail.com", 2, "1234567890", "admin"),
("Admin", "Admin", "admin.docente@gmail.com", 3, "1234567890", "admin"),
("Admin", "Admin", "admin.centralino@gmail.com", 4, "1234567890", "admin"),
("Francesco", "Pirra", "pirra.francesco@iisviolamarchesini.edu.it", 3, "1234567890", "pirra"),
("Valeria", "Baleanu", "baleanu.valeria@iisviolamarchesini.edu.it", 3, "1234567890", "baleanu"),
("Nicolo", "Ciancaglia", "ciancaglia.nicolo@iisviolamarchesini.edu.it", 3, "1234567890", "ciancaglia");


insert into dbmuybgxgpjqvi.giorno (nome)
values ("lunedi"), ("martedi"), ("mercoledi"), ("giovedi"), ("venerdi"), ("sabato");


insert into dbmuybgxgpjqvi.ora (id, data_inizio, data_fine)
values (1, "8:00", "9:30"), (2, "9:30", "10:30"), (3, "10:30", "11:30"), (4, "11:30", "12:30"), (5, "12:30", "13:30");


insert into dbmuybgxgpjqvi.motivazione (nome)
values ("Salute"), ("Matrimonio"), ("Maternità"), ("Corsi di aggiornamento"), ("Motivi familiari"), ("Lutto");


insert into dbmuybgxgpjqvi.tipo_disponibilita (nome)
values ("compresenza"), ("ora buca"), ("temporanea"), ("permanente");


insert into dbmuybgxgpjqvi.compresenza (id_teorico, id_laboratorio, ora, giorno)
values (7, 8, 1, 3), (7, 1, 1, 2), (6, 8, 2, 1);


insert into dbmuybgxgpjqvi.disponibilita (docente, tipo_disponibilita, giorno, ora, data_inizio, data_fine)
values (8, 1, null, null, '2026-04-26 08:00:00', '2026-04-20 09:30:00'), (8, 1, null, null, '2025-04-20 08:00:00', '2025-04-20 09:30:00'),
(7, 4, 6, 5, null, null), (4, 4, 6, 5, null, null);


insert into dbmuybgxgpjqvi.assenza (docente, motivazione, certificato_medico, data_inizio, data_fine, nota)
values (1, 1, "0123456789", "2024-04-20 8:00:00", "2024-04-20 13:30:00", null),
(1, 1, "0123456789", "2020-05-23 8:00:00", "2020-05-20 13:30:00", null),
(7, 4, null, "2028-04-20 9:30:00", "2028-04-20 10:30:00", "la classe deve svolgere i compiti assegnati"),
(6, 5, "0123456789", "2024-04-20 8:00:00", "2026-04-20 13:30:00", null),
(6, 5, "0123456789", "2028-04-20 9:30:00", "2028-04-20 10:30:00", "la 5E deve svolgere le simulazioni invalsi di italiano");


insert into dbmuybgxgpjqvi.supplenza (assenza, ora, data_supplenza, supplente, da_retribuire, non_necessaria, nota)
values (1, 1, '2024-04-20', null, null, true, "la classe 5E entra alle 9:30"), 
(1, 2, '2024-04-20', 6, false, false, "la 1A è stata trasferita in aula 30"),
(1, 3, '2024-04-20', 8, false, false, null),
(1, 4, '2024-04-20', 6, true, false, null),
(1, 5, '2024-04-20', null, null, true, "la classe 3F esce alle 12:30"),
(3, 2, '2028-04-20', 1, false, false, null);


insert into dbmuybgxgpjqvi.banca_ore (tipo_ora, docente, numero_ore, nota)
values ("da recuperare", 1, 2, "da recuperare possibilmente entro il 24 maggio"),
("straordinario", 7, 3, "lezioni al serale del 14 aprile 2023"),
("da recuperare", 8, 5, null);

insert into dbmuybgxgpjqvi.reset (id_utente, `password`, data_richiesta, completato)
values (1, "ABCDE", "2024-04-20 8:00:00", false),
(3, "admin", "2024-04-10 10:00:00", true),
(6, "adminnn", "2024-04-26 7:52:00", false);