CREATE OR REPLACE VIEW request AS
SELECT `pend_id` as id,
    `pend_email` as email,
    `pend_ip` as ip,
    `pend_name` as name,
    `pend_cmt` as comment,
    `pend_status` as status,
    `pend_date` as date,
    `pend_checksum` as checksum,
    `pend_emailsent` as emailsent,
    `pend_mailconfirm` as emailconfirm,
    `pend_reserved` as reserved,
    `pend_useragent` as useragent,
    `pend_proxyip` as forwardedip
FROM `acc_pend`;