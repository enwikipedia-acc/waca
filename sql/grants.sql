revoke all privileges on production.* from production;
grant select,update,insert,delete on `antispoofcache` to production;
grant select,insert on `applicationlog` to production;
grant insert on `audit` to production;
grant select,update,insert on `ban` to production;
grant select,update,insert on `comment` to production;
grant select,update,insert on `emailtemplate` to production;
grant select,update,insert,delete on `geolocation` to production;
grant select,update,insert on `interfacemessage` to production;
grant select,insert on `log` to production;
grant select,insert on `ratelimit` to production;
grant select,update,insert,delete on `rdnscache` to production;
grant select,update,insert,delete on `request` to production;
grant select,update on `schemaversion` to production;
grant select,update,insert on `user` to production;
grant select,update,insert on `welcometemplate` to production;
grant select,update,insert,delete on `xfftrustcache` to production;

