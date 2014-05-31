rename table acc_template to welcometemplate;
alter table welcometemplate
change column template_id id int(11) not null auto_increment ,
change column template_usercode usercode text not null ,
change column template_botcode botcode text not null;
create or replace view acc_template as select t.id template_id, t.usercode template_usercode, t.botcode template_botcode from welcometemplate t;
