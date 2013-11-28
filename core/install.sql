CREATE TABLE IF NOT EXISTS `lq_authors` (
  `authorId` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `slugName` varchar(255) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `quotesNumber` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`authorId`),
  UNIQUE KEY `slugName` (`slugName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

CREATE TABLE IF NOT EXISTS `lq_marks` (
  `markId` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `quoteId` mediumint(6) unsigned NOT NULL,
  `topicId` mediumint(6) unsigned NOT NULL,
  PRIMARY KEY (`markId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

CREATE TABLE IF NOT EXISTS `lq_origins` (
  `originId` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`originId`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

CREATE TABLE IF NOT EXISTS `lq_quotes` (
  `quoteId` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL,
  `authorId` mediumint(6) unsigned NOT NULL,
  `originId` mediumint(6) unsigned NOT NULL,
  `lang` char(2) NOT NULL DEFAULT 'en',
  `status` enum('submitted','refused','revised','published') NOT NULL DEFAULT 'submitted',
  `submissionDate` int(10) unsigned NOT NULL,
  PRIMARY KEY (`quoteId`),
  KEY `submissionDate` (`submissionDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

CREATE TABLE IF NOT EXISTS `lq_topics` (
  `topicId` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `slug` varchar(30) NOT NULL,
  `quotesNumber` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topicId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

INSERT INTO `lq_authors` (`authorId`, `slugName`, `fullName`, `quotesNumber`) VALUES
(1, 'anonymous', 'Anonymous', 247);

INSERT INTO `lq_marks` (`markId`, `quoteId`, `topicId`) VALUES
(1, 1, 1);

INSERT INTO `lq_origins` (`originId`, `name`, `type`, `url`) VALUES
(1, 'Unknown', 'miscellaneous', NULL);

INSERT INTO `lq_quotes` (`quoteId`, `text`, `authorId`, `originId`, `lang`, `status`, `submissionDate`) VALUES
(1, 'A common mistake that people make when trying to design something completely foolproof is to underestimate the ingenuity of complete fools.', 1, 1, 'en', 'published', 1383167235);

INSERT INTO `lq_topics` (`topicId`, `name`, `slug`, `quotesNumber`) VALUES
(1, 'Fool', 'fool', 1);
