DROP TABLE IF EXISTS `acc_template`;
CREATE TABLE `acc_template` (
  `template_id` tinytext NOT NULL,
  `template_usercode` tinytext NOT NULL,
  `template_botcode` tinytext NOT NULL,
  PRIMARY KEY (`template_id`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `acc_template` (`template_id`, `template_usercode`, `template_botcode`) VALUES
('welcome', '{{welcome|user}} ~~~~', '{{subst:Welcome|$username}}$signature ~~~~~'),
('welcomeg', '{{welcomeg|user}} ~~~~', '== Welcome! ==\n\n{{subst:Welcomeg|$username|sig=$signature ~~~~~}}'),
('welcome-personal', '{{welcome-personal|user}} ~~~~', '{{subst:Welcome-personal|$username||$signature ~~~~~}}'),
('werdan7', '{{User:Werdan7/W}} ~~~~', '{{subst:User:Werdan7/Wel}}$signature ~~~~~'),
('welcomemenu', '{{WelcomeMenu|sig=~~~~}}', '== Welcome! ==\n\n{{subst:WelcomeMenu|sig=$signature ~~~~~}}'),
('welcomeicon', '{{WelcomeIcon}} ~~~~', '== Welcome! ==\n\n{{subst:WelcomeIcon}} $signature ~~~~~'),
('welcomeshout', '{{WelcomeShout|user}} ~~~~', '{{subst:WelcomeShout|$username}} $signature ~~~~~'),
('welcomeshort', '{{Welcomeshort|user}} ~~~~', '{{subst:Welcomeshort|$username}} $signature ~~~~~'),
('welcomesmall', '{{WelcomeSmall|user}} ~~~~', '{{subst:Welcomesmall|$username}} $signature ~~~~~'),
('hopes', '{{Hopes Welcome}} ~~~~', '{{subst:Hopes Welcome}} $signature ~~~~~'),
('w-riana', '{{User:Riana/Welcome|name=user|sig=~~~~}}', '== Welcome! ==\n\n{{subst:User:Riana/Welcome|name=$username|sig=$signature ~~~~~}}'),
('wodup', '{{User:WODUP/Welcome}} ~~~~', '{{subst:User:WODUP/Welcome}} $signature ~~~~~'),
('w-screen', '{{w-screen|sig=~~~~}}', '== Welcome! ==\n\n{{subst:w-screen|sig=$signature ~~~~~}}'),
('williamh', '{{User:WilliamH/Welcome|user}} ~~~~', '{{subst:User:WilliamH/Welcome|$username}} $signature ~~~~~'),
('malinaccier', '{{User:Malinaccier/Welcome|~~~~}}', '{{subst:User:Malinaccier/Welcome|$signature ~~~~~}}'),
('welcome!', '{{Welcome!|from=user|ps=~~~~}}', '== Welcome! ==\n\n{{subst:Welcome!|from=$username|ps=$signature ~~~~~}}'),
('laquatique', '{{User:L''Aquatique/welcome}} ~~~~', '{{subst:User:L''Aquatique/welcome}} $signature ~~~~~'),
('coffee', '{{User:Coffee/welcome|user|||~~~~}}', '{{subst:User:Coffee/welcome|$username|||$signature ~~~~~}}'),
('matt-t', '{{User:Matt.T/C}} ~~~~', '{{subst:User:Matt.T/C}} $signature ~~~~~'),
('staffwaterboy', '{{User:Staffwaterboy/Welcome}} ~~~~', '{{subst:User:Staffwaterboy/Welcome}} $signature ~~~~~'),
('maedin', '{{User:Maedin/Welcome}} ~~~~', '{{subst:User:Maedin/Welcome}} $signature ~~~~~'),
('chzz', '{{User:Chzz/botwelcome|name=user|sig=~~~~}}', '{{subst:User:Chzz/botwelcome|name=$username|sig=$signature ~~~~~}}'),
('phantomsteve', '{{User:Phantomsteve/bot welcome}} ~~~~', '{{subst:User:Phantomsteve/bot welcome}} $signature ~~~~~'),
('hi878', '{{User:Hi878/Welcome|user|~~~~}}', '{{subst:User:Hi878/Welcome|$username|$signature ~~~~~}}'),
('fridaesdoom', '{{User:Fridae\'sDoom/Welcome-message}}', '{{subst:User:Fridae\'sDoom/Welcome-message|user=$username|sig=$signature ~~~~~}}'),
('rockdrum', '{{User:Rock drum/ACCWelcome|user=user|sig=~~~~}}', '{{subst:User:Rock drum/ACCWelcome|user=$username|sig=$signature ~~~~~}}');