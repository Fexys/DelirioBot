<?php

date_default_timezone_set('Europe/Rome');

define('VERSION', '0.1.0a');

define('LOG_DIR', 'logs/');
define('COMMAND_DIR', 'commands/');
define('DATABASE_DIR', 'database/');

$config['server']	= 'irc.freenode.net';
$config['port']		= 6667;
$config['channel']	= '#DeliriNotturni';

$config['bot']['nickname'] = 'ilDelirante';
$config['bot']['realname'] = 'ilDelirante [ver ' . VERSION . ']';
$config['bot']['username'] = 'delirio';
$config['bot']['password'] = '';
$config['bot']['usermode'] = 8;

$config['logger']['nickname'] = 'DelirioLogger';
$config['logger']['realname'] = 'DelirioLogger [ver ' . VERSION . ']';
$config['logger']['username'] = 'delirio';
$config['logger']['password'] = '';
$config['logger']['usermode'] = 8;