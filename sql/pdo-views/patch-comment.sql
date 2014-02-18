CREATE VIEW comment AS
SELECT cmt_id as id,
    cmt_time as time,
    id as user,
    cmt_comment as comment,
    cmt_visability as visibility,
    pend_id as request
FROM acc_cmt
INNER JOIN user on username = cmt_user;