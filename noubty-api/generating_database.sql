CREATE TABLE Service (
	id INTEGER PRIMARY KEY,
	title VARCHAR(250),
	current_turn INTEGER
);

CREATE TABLE Turn (
	id INTEGER PRIMARY KEY,
	service_id INTEGER,
	turn INTEGER ,
	CONSTRAINT fk_turn_service FOREIGN KEY (service_id) REFERENCES Service(id) ON DELETE CASCADE
);
