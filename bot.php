<?php
   include_once('SmartIRC.php');

/* Variabile che contiene il nome del chan */
 //$chan = "#DeliriNotturni";

 $chan = "#delirinotturni2";

 /*Next, create the bot-class:*/
class Delirio {

/*A Greet function*/

    function onjoin_greeting(&$irc, &$data)
    { // if we join, don't greet ourself, just jump out via return
        if ($data->nick == $irc->_nick)
           return;

        // now check if this is the right channel
        if ($data->channel == $chan)
        // it is, lets greet the joined user
           $irc->message(SMARTIRC_TYPE_CHANNEL, $chan, 'hi '.$data->nick);
    }


/*Quit Function*/

    function quit(&$irc, &$data)
    {
        // Only run the command if the nick is an owner.
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
             $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "adios.");
             exit();
             Return ;
        }
    }

/*Kick-Function*/

    function kick(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1],$data->messageex[2])) {
                $nickname = $data->messageex[1];
                $reason = $data->messageex[2];
                $channel = $data->channel;
                $irc->kick($channel, $nickname, $reason);
            } else {
                $irc->message( $data->type, $data->nick, 'Invalid Parameter' );
                $irc->message( $data->type, $data->nick, 'use: !kick $nick' );
            }
        }
    }

/*If the bot gets kicked, let it rejoin*/

    function kick_response(&$irc, &$data)
    { //if bot is kicked
       if ($data->nick == $irc->_nick) {
           $irc->join(array($chan));
           $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "dont kick me... please");
           Return ;
       }
    }


/*Function to change channelmodes*/

    function mode($channel, $newmode = null, $priority = SMARTIRC_MEDIUM)
    {
       if ($newmode !== null) {
          $irc->_send('MODE '.$channel.' '.$newmode, $priority);
       } else {
          $irc->_send('MODE '.$channel, $priority);
       }
    }

/*Devoice Function*/

    function devoice(&$irc, &$data)
    {
       if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
       if(isset($data->messageex[1])) {
       $nickname = $data->messageex[1];
           $channel = $data->channel;
           $irc->devoice($channel, $nickname );
       }
       }
    }

/*Op Function*/

    function op(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $nickname = $data->messageex[1];
                $channel = $data->channel;
                $irc->op($channel, $nickname );
            }
        }
    }

/*Deop Function*/

    function deop(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $nickname = $data->messageex[1];
                $channel = $data->channel;
                $irc->deop($channel, $nickname );
            }
        }
    }

/*Join Function*/

    function join(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $channel = $data->messageex[1];
                $irc->join($channel);
            }
        }
    }

/*Part Function*/

    function part(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $channel = $data->messageex[1];
                $irc->part($channel);
            }
        }
    }

/*Function to rejoin a channel*/

    function rejoin(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $channel = $data->messageex[1];
                $irc->part($channel);
                $irc->join($channel);
            }
        }
    }

/*Ban Function*/

    function ban(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $hostmask = $data->messageex[1];
                $channel = $data->channel;
                $irc->ban($channel, $hostmask);
            } else {
                $irc->message( $data->type, $data->nick, 'Invalid Parameter' );
                $irc->message( $data->type, $data->nick, 'use: !ban $nick' );
            }
        }
    }

/*Function for the nickchange-command*/

    function nick(&$irc, &$data)
    {
        if(($data->nick == "Mte90" || $data->nick == "exOOr" || $data->nick == "PoOoL_" || $data->nick == "Angie")) {
            if(isset($data->messageex[1])) {
                $newnick = $data->messageex[1];
                $channel = $data->channel;
                $irc->changeNick($newnick );
            }
        }
    }

/*Function that does the actual nickchange*/

    function changeNick($newnick, $priority = SMARTIRC_MEDIUM)
    {
        $this->_send('NICK '.$newnick, $priority);
        $this->_nick = $newnick;
    }

/*End the Bot-class*/

}

/*Start the bot and set some settings*/

$bot = new Delirio();
$irc = new Net_SmartIRC();
$irc->setDebug(SMARTIRC_DEBUG_ALL);
$irc->setUseSockets(TRUE);

/*Bind IRC Commands to the above defined functions and end the PHP-file*/

$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting');
$irc->connect('irc.freenode.org', 6667);
$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!restart', $bot, 'quit');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ban', $bot, 'ban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick');


// nick , nome , realname , ident, senha do nick
$irc->login('Delirante', 'ilDelirante'.'delirio', 8, 'delirioNetwork','');
$irc->join($chan);
$irc->listen( );
$irc->disconnect( );

?>
