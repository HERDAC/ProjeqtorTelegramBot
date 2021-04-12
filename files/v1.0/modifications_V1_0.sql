INSERT INTO menu (id, name, idMenu, type, sortOrder, level, idle, menuClass, isAdminMenu, isLeavesSystemMenu) VALUES
( 200000001, 'menuTicketTemplate', 88, 'object', 694, 'Project', 0, 'Automation', 0, 0);

INSERT INTO navigation (id, name, idParent, idMenu, idReport, sortOrder) VALUES
(200000001, 'menuTicketTemplate', 129, 200000001, 0, 51);

CREATE TABLE tickettemplate (
	id INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	idTicketType INT(12) UNSIGNED DEFAULT NULL COMMENT '12',
	idProject INT(12) UNSIGNED DEFAULT NULL COMMENT '12',
	idle INT(1) UNSIGNED DEFAULT '0' COMMENT '1',
	idContext1 INT(12) UNSIGNED DEFAULT NULL,
	idContext3 INT(12) UNSIGNED DEFAULT NULL,
	idActivity INT(12) UNSIGNED DEFAULT NULL COMMENT '12',
	idUrgency INT(12) UNSIGNED DEFAULT NULL COMMENT '12',
	PRIMARY KEY (id),
	KEY ticketTicketType (idTicketType),
	KEY projectProject (idProject),
	KEY ticketActivity (idActivity),
	KEY ticketUrgency (idUrgency)
);