<?php

include('DelirioCore.php');

$irc = &new Net_SmartIRC();
$bot = &new DelirioBot($irc);