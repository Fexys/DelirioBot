<?php

include('DelirioLogger.php');

$irc = &new Net_SmartIRC();
$logger = &new DelirioLogger($irc);