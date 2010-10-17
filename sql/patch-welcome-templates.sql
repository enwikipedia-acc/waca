DROP TABLE IF EXISTS `acc_template`;
CREATE TABLE `acc_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `template_usercode` tinytext NOT NULL,
  `template_botcode` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `acc_template` (`template_usercode`, `template_botcode`) VALUES
('{{welcome|user}} ~~~~', '{{subst:Welcome|$username}}$signature'),
('{{welcomeg|user}} ~~~~', '== Welcome! ==\n\n{{subst:Welcomeg|$username|sig=$signature}}'),
('{{welcome-personal|user}} ~~~~', '{{subst:Welcome-personal|$username||$signature}}'),
('{{User:Werdan7/W}} ~~~~', '{{subst:User:Werdan7/Wel}}$signature'),
('{{WelcomeMenu|sig=~~~~}}', '== Welcome! ==\n\n{{subst:WelcomeMenu|sig=$signature}}'),
('{{WelcomeIcon}} ~~~~', '== Welcome! ==\n\n{{subst:WelcomeIcon}} $signature'),
('{{WelcomeShout|user}} ~~~~', '{{subst:WelcomeShout|$username}} $signature'),
('{{Welcomeshort|user}} ~~~~', '{{subst:Welcomeshort|$username}} $signature'),
('{{WelcomeSmall|user}} ~~~~', '{{subst:Welcomesmall|$username}} $signature'),
('{{Hopes Welcome}} ~~~~', '{{subst:Hopes Welcome}} $signature'),
('{{User:Riana/Welcome|name=user|sig=~~~~}}', '== Welcome! ==\n\n{{subst:User:Riana/Welcome|name=$username|sig=$signature}}'),
('{{User:WODUP/Welcome}} ~~~~', '{{subst:User:WODUP/Welcome}} $signature'),
('{{w-screen|sig=~~~~}}', '== Welcome! ==\n\n{{subst:w-screen|sig=$signature}}'),
('{{User:WilliamH/Welcome|user}} ~~~~', '{{subst:User:WilliamH/Welcome|$username}} $signature'),
('{{User:Malinaccier/Welcome|~~~~}}', '{{subst:User:Malinaccier/Welcome|$signature}}'),
('{{Welcome!|from=user|ps=~~~~}}', '== Welcome! ==\n\n{{subst:Welcome!|from=$username|ps=$signature}}'),
('{{User:L''Aquatique/welcome}} ~~~~', '{{subst:User:L''Aquatique/welcome}} $signature'),
('{{User:Coffee/welcome|user|||~~~~}}', '{{subst:User:Coffee/welcome|$username|||$signature}}'),
('{{User:Matt.T/C}} ~~~~', '{{subst:User:Matt.T/C}} $signature'),
('{{User:Staffwaterboy/Welcome}} ~~~~', '{{subst:User:Staffwaterboy/Welcome}} $signature'),
('{{User:Maedin/Welcome}} ~~~~', '{{subst:User:Maedin/Welcome}} $signature'),
('{{User:Chzz/botwelcome|name=user|sig=~~~~}}', '{{subst:User:Chzz/botwelcome|name=$username|sig=$signature}}'),
('{{User:Phantomsteve/bot welcome}} ~~~~', '{{subst:User:Phantomsteve/bot welcome}} $signature'),
('{{User:Hi878/Welcome|user|~~~~}}', '{{subst:User:Hi878/Welcome|$username|$signature}}'),
('{{User:Fridae\'sDoom/Welcome-message}}', '{{subst:User:Fridae\'sDoom/Welcome-message|user=$username|sig=$signature}}'),
('{{User:Rock drum/ACCWelcome|user=user|sig=~~~~}}', '{{subst:User:Rock drum/ACCWelcome|user=$username|sig=$signature}}');
