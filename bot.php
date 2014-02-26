<?php

include('DelirioBot.php');
$irc = &new Net_SmartIRC();
$bot = &new DelirioBot($irc);