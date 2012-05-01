<?php

include('DelirioLogger.php');

$irc = &new Net_SmartIRC();
$bot = &new DelirioLogger($irc);