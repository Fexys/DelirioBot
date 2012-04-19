<?php
include_once('DelirioCore.php');

//Variabile che contiene il nome del chan
$chan = '#DeliriNotturni';

//Creiamo la classe e facciamo partire il bot
$bot = new Delirio( );
$bot->setVar();
$irc = new Net_SmartIRC( );
$irc->startBenchmark();
$irc->setDebug(SMARTIRC_DEBUG_ALL);
$irc->setBenchmark(TRUE);
$irc->setUseSockets(TRUE);
$irc->setUserSyncing(TRUE);
$irc->setChannelSyncing(TRUE);
$irc->setSenddelay(500);

//Configuriamo i vari comandi con le funzioni
$irc->connect('irc.freenode.org', 6667);
$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting');
$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'insulto_');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!*', $bot, 'check');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kill$', $bot, 'kill');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rm', $bot, 'ban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!refresh', $bot, 'refresh');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!saluta', $bot, 'saluta');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!help$', $bot, 'help');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!who\s*$', $bot, 'who');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!whoami', $bot, 'whoami');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!versione$', $bot, 'versione');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!github$', $bot, 'github');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!insulta', $bot, 'insulta');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ls', $bot, 'ls');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!stop', $bot, 'stop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $bot, 'paste');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $bot, 'google');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!yt', $bot, 'yt');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!porn', $bot, 'porn');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deb', $bot, 'deb');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rpm', $bot, 'rpm');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $bot, 'pkg');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!translate', $bot, 'translate');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $bot, 'dado');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!inalbera', $bot, 'inalbera');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!battezza', $bot, 'battezza');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!noi', $bot, 'noi');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!birra', $bot, 'birra');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!muori', $bot, 'muori');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!gaio', $bot, 'gaio');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!tumblr', $bot, 'tumblr');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!amore', $bot, 'amore');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nutella', $bot, 'nutella');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!supercazzola', $bot, 'supercazzola');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!man', $bot, 'man');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ddos', $bot, 'ddos');

//Nickname, Nome , RealName, Ident, Password
$irc->login('ilDelirante', 'ilDelirante'.'delirio', 8, 'delirio', '');
$irc->join($chan);
$irc->listen();
$irc->disconnect();