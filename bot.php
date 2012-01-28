<?php
// Includo la classe SmartIRC che è presente nei repo di Debian ma è documentata da cani
include_once( 'SmartIRC.php' );
include_once('SmartIRC/irccommands.php');
include_once('SmartIRC/messagehandler.php'); //queste funzioni sono extra ma non funzionano

//includo il file con le bio
include( 'bio.php' );

// Variabile che contiene il nome del chan
$chan = "#DeliriNotturni";

//$chan = "#delirinotturni2";

// Classe con le funzioni del ilDelirante
class Delirio {
	//Variabile che conterrà i vari op
	var $op = array();
	//Settiamo le varie proprietà del bot
	function setVar( ) {
		$this->op = file( 'op.php' );
	}
	//Saluto chi entra gentilmente
	function onjoin_greeting( &$irc, &$data ) {
		if( $data->nick == $irc->_nick ) {return;}
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Ciao '.$data->nick );
	}
	//Spengo il bot ***Non Funziona***
	function restart( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op , true) ) {
			//$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Addio mondo crudele!" );
			$irc->quit("Addio mondo crudele!");
			/*exit();
			return ;*/
		} else {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Chi ti credi di essere per darmi questi comandi?????");
		}
	}
	//Kick
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
	//Se il bot è kikato rientra
	function kick_response( &$irc, &$data ) {
		//if bot is kicked

		if( $data->nick == $irc->_nick ) {
			$irc->join( array( $chan ) );
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "dont kick me... please" );
			Return;
		}
	}
	//modalità del chan
	function mode( $channel, $newmode = null, $priority = SMARTIRC_MEDIUM ) {
		if( $newmode !== null ) {
			$irc->_send( 'MODE '.$channel.' '.$newmode, $priority );
		}
		else {
			$irc->_send( 'MODE '.$channel, $priority );
		}
	}
	//Devoice
	function devoice( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->devoice( $channel, $nickname );
			}
		}
	}
	//Op
	function op( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->op( $channel, $nickname );
			}
		}
	}
	//Deop
	function deop( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->deop( $channel, $nickname );
			}
		}
	}
	//Join
	function join( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->join( $channel );
			}
		}
	}
	//Part ***Che cavolo è???***
	function part( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->part( $channel );
			}
		}
	}
	//Rejoin
	function rejoin( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->part( $channel );
				$irc->join( $channel );
			}
		}
	}
	//Ban ***deve essere insultante***
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
	//Perchè cambiare il nick a ildelirante?
	function nick( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$newnick = $data->messageex[1];
				$channel = $data->channel;
				$irc->changeNick( $newnick );
			}
		}
	}
	//Saluta molto educatamente
	function saluta( &$irc, &$data ) {
			if( isset( $data->messageex[1] ) ) {
				$poggio=$data->messageex;
				unset($poggio[0]);
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Fottiti ".implode(" ",$poggio) );
			}
	}
	//Stampa la bio dell'utente o del parametro passato
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
	//Lista dei comandi
	function help( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Comandi: !saluta, !help, !whoami, !versione, !github, !who" );
	}
	//Versione
	function versione( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Sono cavoli miei... 0.0.3" );
	}
	//Link su Github del bot
	function github( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Sorgenti: https://github.com/Mte90/Delirante" );
	}
	//Utenti nel database
	function who( &$irc, &$data ) {
		global $bio_tot;
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Utenti nel database: ".implode(", ",$bio_tot) );
	}
	//Insulta ***in lavorazione***
	function insulta( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $irc->_event_nick($data));
	}
	//Cambia il nick attuale??
	function changeNick( $newnick, $priority = SMARTIRC_MEDIUM ) {
		$this->_send( 'NICK '.$newnick, $priority );
		$this->_nick = $newnick;
	}

	/*End the Bot-class*/
}
//Impostiamo e facciamo partire il bot
$bot = new Delirio( );
$bot->setVar( );
$irc = new Net_SmartIRC( );
$irc->startBenchmark();
$irc->setDebug( SMARTIRC_DEBUG_ALL );
$irc->setBenchmark(TRUE);
$irc->setUseSockets( TRUE );
//$irc->setUserSyncing( TRUE );
//Configuriamo i vari comandi con le funzioni
$irc->connect( 'irc.freenode.org', 6667 );
$irc->registerActionhandler( SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting' );
$irc->registerActionhandler( SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '!restart', $bot, 'restart' );
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
// nick , nome , realname , ident, boh
$irc->login( 'ilDelirante', 'ilDelirante'.'delirio', 8, 'delirioNetwork', '' );
$irc->join( $chan );
$irc->listen( );
$irc->disconnect( );
?>