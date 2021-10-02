--TelegramDisplayTemplate
CREATE TABLE telegramdisplaytemplate (
	id int(12) unsigned NOT NULL AUTO_INCREMENT,
	name varchar(100) NOT NULL,
	template varchar(500) DEFAULT NULL,
	idMailable int(12) DEFAULT NULL COMMENT '12',
	idType int(12) unsigned DEFAULT NULL COMMENT '12',
	idStatus int(12) unsigned DEFAULT NULL COMMENT '12',
	idle int(1) unsigned DEFAULT 0 COMMENT '1',
	butReturn int(1) unsigned DEFAULT 0 COMMENT '1',
	butAssign int(1) unsigned DEFAULT 0 COMMENT '1',
	butWork int(1) unsigned DEFAULT 0 COMMENT '1',
	butReply int(1) unsigned DEFAULT 0 COMMENT '1',
	butSend int(1) unsigned DEFAULT 0 COMMENT '1',
	PRIMARY KEY (id),
	KEY `telegramdisplaytemplateStatus` (`idStatus`),
	KEY `telegramdisplaytemplateMailable` (`idMailable`)
);

INSERT INTO menu (id, name, idMenu, type, sortOrder, level, idle, menuClass, isAdminMenu, isLeavesSystemMenu) VALUES ( 200000002, 'menuTelegramDisplayTemplate', 88, 'object', 687, 'ReadWriteAutomation', 0, 'Automation', 0, 0);
 
INSERT INTO navigation (name, idParent, idMenu, idReport, sortOrder) VALUES ('menuTelegramDisplayTemplate', 129, 200000002, 0, 22);


--TelegramSummaryTemplate
CREATE TABLE telegramsummarytemplate (
	id int(12) unsigned NOT NULL AUTO_INCREMENT,
	template varchar(500) DEFAULT NULL,
	idMailable int(12) DEFAULT NULL COMMENT '12',
	idle int(1) unsigned DEFAULT 0 COMMENT '1',
	PRIMARY KEY (id),
	KEY `telegramsummarytemplateMailable` (`idMailable`)
);

INSERT INTO menu (id, name, idMenu, type, sortOrder, level, idle, menuClass, isAdminMenu, isLeavesSystemMenu) VALUES ( 200000003, 'menuTelegramSummaryTemplate', 88, 'object', 688, 'ReadWriteAutomation', 0, 'Automation', 0, 0);
 
INSERT INTO navigation (name, idParent, idMenu, idReport, sortOrder) VALUES ('menuTelegramSummaryTemplate', 129, 200000003, 0, 23);


--TelegramBotUser
CREATE TABLE telegrambotuser (
	id INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
	idUser INT(12) UNSIGNED NOT NULL,
	chatId INT UNSIGNED NOT NULL,
	state INT NOT NULL DEFAULT 0,
	buttonMsgId INT DEFAULT NULL,
	`data` LONGTEXT DEFAULT '',
	PRIMARY KEY (id),
	KEY `telegrambotuserUser` (`idUser`)
);
