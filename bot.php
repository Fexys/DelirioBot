<?php
date_default_timezone_set('Europe/Rome');

include_once('DelirioCore.php');
require_once('config.php');


//Creiamo la classe e facciamo partire il bot
$bot = &new DelirioBot();
$bot->setVars();
$irc = &new Net_SmartIRC();
$irc->startBenchmark();
$irc->setDebug(SMARTIRC_DEBUG_ALL);
$irc->setBenchmark(TRUE);
$irc->setUseSockets(TRUE);
$irc->setUserSyncing(TRUE);
$irc->setChannelSyncing(TRUE);
$irc->setSendDelay(500);

//Configuriamo i vari comandi con le funzioni
$irc->connect($config['server'], $config['port']);


$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'join_greeting');
$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'mention_insult');

$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/alphacode/i', $bot, 'test_function');

$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!info$', $bot, 'info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!tumblr', $bot, 'tumblr');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!help$', $bot, 'help');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!man', $bot, 'man');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!saluta', $bot, 'give_greeting');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ls$', $bot, 'online_users_list');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!who$', $bot, 'who');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!whoami', $bot, 'whoami');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!twitter', $bot, 'twitter');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!noi', $bot, 'noi');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!insulta', $bot, 'insulta');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!inalbera', $bot, 'inalbera');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!muori', $bot, 'muori');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!supercazzola', $bot, 'supercazzola');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!calcio', $bot, 'calcio');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!gaio$', $bot, 'gaio');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!amore', $bot, 'amore');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!birra', $bot, 'birra');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nutella', $bot, 'nutella');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $bot, 'google_search');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!yt', $bot, 'youtube_search');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!porn', $bot, 'porn_search');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!anime', $bot, 'anime_serch');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $bot, 'packages_search');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!translate', $bot, 'translate');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $bot, 'paste');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $bot, 'dado');

$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!set', $bot, 'settings');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!refresh$', $bot, 'refresh');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!roulette$', $bot, 'roulette');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!clear$', $bot, 'clear');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!mute', $bot, 'mute');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!unmute', $bot, 'unmute');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ban', $bot, 'ban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!battezza', $bot, 'battezza');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kill$', $bot, 'kill');


$irc->login($config['nickname'], $config['realname'], $config['usermode'], $config['username'], $config['password']);
$irc->join($config['channel']);
//$bot->startlog($irc);
$irc->listen();
$irc->disconnect();
//$bot->stoplog();