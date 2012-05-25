-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 25 mag, 2012 at 08:59 AM
-- Versione MySQL: 5.5.9
-- Versione PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `delirinotturni`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_deaths`
--

CREATE TABLE `bot_deaths` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `death` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_highscores`
--

CREATE TABLE `bot_highscores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `roulette_survivor` int(10) unsigned NOT NULL DEFAULT '0',
  `roulette_kick` int(10) unsigned NOT NULL DEFAULT '0',
  `trivia_score` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_insults`
--

CREATE TABLE `bot_insults` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `insult` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_manual`
--

CREATE TABLE `bot_manual` (
  `command` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` text COLLATE utf8_bin NOT NULL,
  `synopsis` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`command`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_personal_insults`
--

CREATE TABLE `bot_personal_insults` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `insult` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_quotes`
--

CREATE TABLE `bot_quotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  `quote` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_supercazzole`
--

CREATE TABLE `bot_supercazzole` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `supercazzola` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_trivia`
--

CREATE TABLE `bot_trivia` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  `question` text COLLATE utf8_bin NOT NULL,
  `answer` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_users`
--

CREATE TABLE `bot_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `greeting` text COLLATE utf8_bin NOT NULL,
  `bio` text COLLATE utf8_bin NOT NULL,
  `twitter` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `last_modified` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
