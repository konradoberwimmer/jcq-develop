DROP TABLE IF EXISTS jcq_itemscales;
DROP TABLE IF EXISTS jcq_questionscales;
DROP TABLE IF EXISTS jcq_item;
DROP TABLE IF EXISTS jcq_question;
DROP TABLE IF EXISTS jcq_page;
DROP TABLE IF EXISTS jcq_project;
DROP TABLE IF EXISTS jcq_code;
DROP TABLE IF EXISTS jcq_scale;

CREATE TABLE jcq_scale(
		ID SERIAL,
		name VARCHAR(255) NOT NULL,
		prepost TEXT
);

CREATE TABLE jcq_code(
		ID SERIAL,
		ord INTEGER NOT NULL,
		code INTEGER NOT NULL,
		label TEXT,
		scaleID INTEGER NOT NULL
);

CREATE TABLE jcq_project(
		ID SERIAL,
		name VARCHAR(255) NOT NULL,
		classfile VARCHAR(255) NOT NULL,
		classname VARCHAR(255) NOT NULL,
		description TEXT,
		anonymous BOOLEAN NOT NULL DEFAULT 0,
		multiple BOOLEAN NOT NULL DEFAULT 0
);

CREATE TABLE jcq_page(
		ID SERIAL,
		name VARCHAR(255) NOT NULL,
		ord INTEGER NOT NULL,
		projectID INTEGER NOT NULL
);

CREATE TABLE jcq_question(
		ID SERIAL,
		name VARCHAR(255) NOT NULL,
		ord INTEGER NOT NULL,
		questtype INTEGER NOT NULL,
		datatype SMALLINT NOT NULL DEFAULT 1,
		varname VARCHAR(255),
		mandatory SMALLINT NOT NULL DEFAULT 1,
		text TEXT,
		advise TEXT,
		prepost TEXT,
		width_scale INTEGER,
		alternate_bg SMALLINT,
		pageID INTEGER NOT NULL
);

CREATE TABLE jcq_item(
		ID SERIAL,
		ord INTEGER NOT NULL,
		varname VARCHAR(255) NOT NULL,
		mandatory SMALLINT NOT NULL DEFAULT 1,
		textleft TEXT,
		textright TEXT,
		questionID INTEGER NOT NULL
);

CREATE TABLE jcq_questionscales(
		questionID INTEGER NOT NULL,
		scaleID INTEGER NOT NULL
);

CREATE TABLE jcq_itemscales(
		itemID INTEGER NOT NULL,
		scaleID INTEGER NOT NULL
);


ALTER TABLE jcq_scale ADD CONSTRAINT IDX_jcq_scale_PK PRIMARY KEY (ID);

ALTER TABLE jcq_code ADD CONSTRAINT IDX_jcq_code_PK PRIMARY KEY (ID);
ALTER TABLE jcq_code ADD CONSTRAINT IDX_jcq_code_FK0 FOREIGN KEY (scaleID) REFERENCES jcq_scale (ID);

ALTER TABLE jcq_project ADD CONSTRAINT IDX_jcq_project_PK PRIMARY KEY (ID);

ALTER TABLE jcq_page ADD CONSTRAINT IDX_jcq_page_PK PRIMARY KEY (ID);
ALTER TABLE jcq_page ADD CONSTRAINT IDX_jcq_page_FK0 FOREIGN KEY (projectID) REFERENCES jcq_project (ID);

ALTER TABLE jcq_question ADD CONSTRAINT IDX_jcq_question_PK PRIMARY KEY (ID);
ALTER TABLE jcq_question ADD CONSTRAINT IDX_jcq_question_FK0 FOREIGN KEY (pageID) REFERENCES jcq_page (ID);

ALTER TABLE jcq_item ADD CONSTRAINT IDX_jcq_item_PK PRIMARY KEY (ID);
ALTER TABLE jcq_item ADD CONSTRAINT IDX_jcq_item_FK0 FOREIGN KEY (questionID) REFERENCES jcq_question (ID);

ALTER TABLE jcq_questionscales ADD CONSTRAINT IDX_jcq_questionscales_FK0 FOREIGN KEY (questionID) REFERENCES jcq_question (ID);
ALTER TABLE jcq_questionscales ADD CONSTRAINT IDX_jcq_questionscales_FK1 FOREIGN KEY (scaleID) REFERENCES jcq_scale (ID);

ALTER TABLE jcq_itemscales ADD CONSTRAINT IDX_jcq_itemscales_FK0 FOREIGN KEY (itemID) REFERENCES jcq_item (ID);
ALTER TABLE jcq_itemscales ADD CONSTRAINT IDX_jcq_itemscales_FK1 FOREIGN KEY (scaleID) REFERENCES jcq_scale (ID);

