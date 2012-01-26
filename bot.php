<?php
include_once( 'SmartIRC.php' );
include_once('SmartIRC/irccommands.php');
include_once('SmartIRC/messagehandler.php');

include( 'bio.php' );
/* Variabile che contiene il nome del chan */

$chan = "#DeliriNotturni";

//$chan = "#delirinotturni2";
//*Next, create the bot-class:*/

class Delirio {

	var $op = array();

	function setVar( ) {
		$this->op = file( 'op.php' );
	}

	/*A Greet function*/

	function onjoin_greeting( &$irc, &$data ) {
		if( $data->nick == $irc->_nick ) {return;}
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Ciao '.$data->nick );
	}

	/*Quit Function*/

	function quit( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			//$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Addio mondo crudele!" );
			$irc->quit("Addio mondo crudele!");
			/*exit();
			return ;*/
		} else {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Chi ti credi di essere per darmi questi comandi?????".$this->op.$data->nick );
		}
	}

	/*Kick-Function*/

	function kick( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1], $data->messageex[2] ) ) {
				$nickname = $data->messageex[1];
				$reason   = $data->messageex[2];
				$channel  = $data->channel;
				$irc->kick( $channel, $nickname, $reason );
			}
			else {
				$irc->message( $data->type, $data->nick, 'Invalid Parameter' );
				$irc->message( $data->type, $data->nick, 'use: !kick $nick' );
			}
		}
	}

	/*If the bot gets kicked, let it rejoin*/

	function kick_response( &$irc, &$data ) {
		//if bot is kicked

		if( $data->nick == $irc->_nick ) {
			$irc->join( array( $chan ) );
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "dont kick me... please" );
			Return;
		}
	}

	/*Function to change channelmodes*/

	function mode( $channel, $newmode = null, $priority = SMARTIRC_MEDIUM ) {
		if( $newmode !== null ) {
			$irc->_send( 'MODE '.$channel.' '.$newmode, $priority );
		}
		else {
			$irc->_send( 'MODE '.$channel, $priority );
		}
	}

	/*Devoice Function*/

	function devoice( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->devoice( $channel, $nickname );
			}
		}
	}

	/*Op Function*/

	function op( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->op( $channel, $nickname );
			}
		}
	}

	/*Deop Function*/

	function deop( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->deop( $channel, $nickname );
			}
		}
	}

	/*Join Function*/

	function join( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->join( $channel );
			}
		}
	}

	/*Part Function*/

	function part( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->part( $channel );
			}
		}
	}

	/*Function to rejoin a channel*/

	function rejoin( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->part( $channel );
				$irc->join( $channel );
			}
		}
	}

	/*Ban Function*/

	function ban( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->ban( $channel, $hostmask );
			}
			else {
				$irc->message( $data->type, $data->nick, 'Invalid Parameter' );
				$irc->message( $data->type, $data->nick, 'use: !ban $nick' );
			}
		}
	}

	/*Function for the nickchange-command*/

	function nick( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$newnick = $data->messageex[1];
				$channel = $data->channel;
				$irc->changeNick( $newnick );
			}
		}
	}

	function saluta( &$irc, &$data ) {
			if( isset( $data->messageex[1] ) ) {
				$poggio=$data->messageex;
				unset($poggio[0]);
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Fottiti ".implode(" ",$poggio) );
			}
	}

	function whoami( &$irc, &$data ) {
		global $bio;
			if( isset( $data->messageex[1] ) && isset($bio[$data->messageex[1]]['bio'])) {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $data->messageex[1].": ".$bio[$data->messageex[1]]['bio'] );
			} elseif(!isset($data->messageex[1]) && isset($bio[$data->nick]['bio'])) {
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $data->nick.": ".$bio[$data->nick]['bio'] );
			}else{
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Utente non inserito nel sistema. Tentativo di intrusione rilevato!" );
			}
	}

	function help( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Comandi: !saluta, !help, !whoami, !versione, !github, !who" );
	}

	function versione( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Sono cavoli miei... 0.0.3" );
	}

	function github( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Sorgenti: https://github.com/Mte90/Delirante" );
	}

	function who( &$irc, &$data ) {
		global $bio_tot;
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Utenti nel database: ".implode(", ",$bio_tot) );
	}

	function insulta( &$irc, &$data ) {
	print_r($irc->channel[$data->channel]->user);
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $irc->user);
	}

	/*Function that does the actual nickchange*/

	function changeNick( $newnick, $priority = SMARTIRC_MEDIUM ) {
		$this->_send( 'NICK '.$newnick, $priority );
		$this->_nick = $newnick;
	}

	/*End the Bot-class*/
}

/*Start the bot and set some settings*/

$bot = new Delirio( );
$bot->setVar( );
$irc = new Net_SmartIRC( );
$irc->startBenchmark();
$irc->setDebug( SMARTIRC_DEBUG_ALL );
$irc->setBenchmark(TRUE);
$irc->setUseSockets( TRUE );
$irc->setUserSyncing( TRUE );

$irc->connect( 'irc.freenode.org', 6667 );
$irc->registerActionhandler( SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting' );
$irc->registerActionhandler( SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '!quit', $bot, 'quit' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!ban', $bot, 'ban' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!saluta', $bot, 'saluta' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!help', $bot, 'help' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!who\s*$', $bot, 'who' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!whoami', $bot, 'whoami' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!versione', $bot, 'versione' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!github', $bot, 'github' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!insulta', $bot, 'insulta' );

// nick , nome , realname , ident, senha do nick

$irc->login( 'ilDelirante', 'ilDelirante'.'delirio', 8, 'delirioNetwork', '' );
$irc->join( $chan );
$irc->listen( );
$irc->disconnect( );
?>