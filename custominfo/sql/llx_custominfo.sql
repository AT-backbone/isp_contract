
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Tabellenstruktur für Tabelle `llx_custominfo`
--

CREATE TABLE IF NOT EXISTS `llx_custominfo` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_product` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(250) NOT NULL,
  PRIMARY KEY (`rowid`),
  KEY `fk_product` (`fk_product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `llx_custominfodet` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_user` int(11) NOT NULL,
  `fk_societe` int(11) NOT NULL,
  `fk_product` int(11) NOT NULL,
  `fk_contratdet` int(11) NOT NULL,
  `fk_commandedet` int(11) NOT NULL,
  `fk_custominfo` int(11) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`rowid`),
  UNIQUE KEY `fk_product_2` (`fk_product`,`fk_contratdet`,`fk_commandedet`,`fk_custominfo`),
  KEY `fk_user` (`fk_user`),
  KEY `fk_societe` (`fk_societe`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
