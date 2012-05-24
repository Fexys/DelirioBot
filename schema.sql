-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 24 mag, 2012 at 03:22 AM
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
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `death` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_insults`
--

CREATE TABLE `bot_insults` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `insult` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_personal_insults`
--

CREATE TABLE `bot_personal_insults` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned NOT NULL,
  `insult` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_quotes`
--

CREATE TABLE `bot_quotes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `added` int(10) unsigned NOT NULL,
  `quote` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_supercazzole`
--

CREATE TABLE `bot_supercazzole` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `supercazzola` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_trivia`
--

CREATE TABLE `bot_trivia` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `added` int(10) unsigned NOT NULL,
  `question` text COLLATE utf8_bin NOT NULL,
  `answer` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_trivia_highscore`
--

CREATE TABLE `bot_trivia_highscore` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `score` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bot_users`
--

CREATE TABLE `bot_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `greeting` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `bio` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `twitter` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL,
  `last_modified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=0 ;
