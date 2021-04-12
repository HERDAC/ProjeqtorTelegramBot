ALTER TABLE tickettemplate ADD idResource INT(12) UNSIGNED DEFAULT NULL COMMENT '12' AFTER idProject;
ALTER TABLE tickettemplate ADD KEY ticketResource (idResource);

ALTER TABLE tickettemplate ADD idCriticality INT(12) UNSIGNED DEFAULT NULL COMMENT '12' AFTER idUrgency;
ALTER TABLE tickettemplate ADD KEY ticketCriticality (idCriticality);

