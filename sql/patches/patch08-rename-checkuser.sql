ALTER TABLE user
	CHANGE COLUMN checkuser checkuser INT(1) NOT NULL DEFAULT '0' COMMENT 'Deprecated - OAuth is now used.' ,
	ADD COLUMN root INT(1) NOT NULL DEFAULT 0;
