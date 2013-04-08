DROP TABLE IF EXISTS jcq_itemscales;
DROP TABLE IF EXISTS jcq_questionscales;
DROP TABLE IF EXISTS jcq_item;
DROP TABLE IF EXISTS jcq_question;
DROP TABLE IF EXISTS jcq_page;
DROP TABLE IF EXISTS jcq_project;
DROP TABLE IF EXISTS jcq_code;
DROP TABLE IF EXISTS jcq_scale;

CREATE TABLE jcq_scale(
		ID BIGINT NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		prepost TEXT,
		predefined BOOLEAN NOT NULL DEFAULT 0,
		PRIMARY KEY (ID)
) ENGINE=InnoDB;

CREATE TABLE jcq_code(
		ID BIGINT NOT NULL AUTO_INCREMENT,
		ord INTEGER NOT NULL,
		code INTEGER NOT NULL,
		label TEXT,
		missval BOOLEAN NOT NULL DEFAULT 0,
		scaleID BIGINT NOT NULL,
		PRIMARY KEY (ID),
		FOREIGN KEY (scaleID) REFERENCES jcq_scale(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jcq_project(
		ID BIGINT NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		cssfile VARCHAR(255),
		description TEXT,
		anonymous BOOLEAN NOT NULL DEFAULT 0,
		multiple BOOLEAN NOT NULL DEFAULT 0,
		PRIMARY KEY (ID)
) ENGINE=InnoDB;

CREATE TABLE jcq_import(
		ID BIGINT NOT NULL AUTO_INCREMENT,	
		ord INTEGER NOT NULL,
		filename VARCHAR(255) NOT NULL,
		projectID BIGINT NOT NULL,
		FOREIGN KEY (projectID) REFERENCES jcq_project(ID) ON UPDATE CASCADE ON DELETE CASCADE,
		PRIMARY KEY (ID)
) ENGINE=InnoDB;

CREATE TABLE jcq_page(
		ID BIGINT NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		ord INTEGER NOT NULL,
		projectID BIGINT NOT NULL,
		filter TEXT,
		isFinal BOOLEAN NOT NULL DEFAULT 0,
		PRIMARY KEY (ID),
		FOREIGN KEY (projectID) REFERENCES jcq_project(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jcq_question(
		ID BIGINT NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		ord INTEGER NOT NULL,
		questtype INTEGER NOT NULL,
		datatype SMALLINT NOT NULL DEFAULT 1,
		varname VARCHAR(255),
		mandatory BOOLEAN NOT NULL DEFAULT 1,
		text TEXT,
		advise TEXT,
		prepost TEXT,
		width_question INTEGER DEFAULT 0,
		width_items INTEGER DEFAULT 0,
		width_scale INTEGER DEFAULT 0,
		alternate_bg BOOLEAN DEFAULT 0,
		pageID BIGINT NOT NULL,
		filter TEXT,
		PRIMARY KEY (ID),
		FOREIGN KEY (pageID) REFERENCES jcq_page(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jcq_item(
		ID BIGINT NOT NULL AUTO_INCREMENT,
		ord INTEGER NOT NULL,
		varname VARCHAR(255) NOT NULL,
		mandatory BOOLEAN NOT NULL DEFAULT 1,
		textleft TEXT,
		textright TEXT,
		questionID BIGINT NOT NULL,
		filter TEXT,
		bindingType ENUM('QUESTION','CODE','ITEM') NOT NULL,
		bindingID BIGINT,
		PRIMARY KEY (ID),
		FOREIGN KEY (questionID) REFERENCES jcq_question(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jcq_questionscales(
		questionID BIGINT NOT NULL,
		scaleID BIGINT NOT NULL,
		ord INTEGER NOT NULL DEFAULT 1,
		mandatory BOOLEAN NOT NULL DEFAULT 1,
		FOREIGN KEY (questionID) REFERENCES jcq_question(ID) ON UPDATE CASCADE ON DELETE CASCADE,
		FOREIGN KEY (scaleID) REFERENCES jcq_scale(ID) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

