DROP TABLE IF EXISTS comment;
DROP VIEW IF EXISTS comment;

CREATE TABLE comment AS
SELECT cmt_id as id,
    cmt_time as time,
    id as user,
    cmt_comment as comment,
    cmt_visability as visibility,
    pend_id as request
FROM acc_cmt
INNER JOIN user on username = cmt_user;

ALTER TABLE comment 
CHANGE COLUMN id id INT(11) NOT NULL AUTO_INCREMENT ,
ADD PRIMARY KEY (id);

ALTER TABLE acc_cmt COMMENT = '[DEPRECATED] Request comments' ;
