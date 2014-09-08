SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `coffee_administrators` (
  `userid` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `coffee_schedule` (
  `userid` int(5) NOT NULL,
  `day` tinyint(3) unsigned NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL,
  `mailsent` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `day` (`day`,`month`,`year`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `coffee_transactions` (
  `transactionid` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `comment` text NOT NULL,
  `userid` smallint(5) unsigned NOT NULL,
  `amount` float NOT NULL,
  `time` int(12) unsigned NOT NULL,
  PRIMARY KEY (`transactionid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1965 ;

CREATE TABLE IF NOT EXISTS `coffee_users` (
  `userid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `lastlogin` int(12) unsigned NOT NULL DEFAULT '0',
  `startingbalance` float NOT NULL COMMENT 'User''s starting balance when added to database',
  `weeklypayment` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
