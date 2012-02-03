<?php
// Includo la classe SmartIRC che è presente nei repo di Debian ma è documentata da cani
include_once( 'SmartIRC.php' );
include_once('SmartIRC/irccommands.php');
include_once('SmartIRC/messagehandler.php'); //queste funzioni sono extra ma non funzionano

//includo il file con le bio
include( 'bio.php' );

// Variabile che contiene il nome del chan
$chan = '#DeliriNotturni';

//$chan = '#delirinotturni2';

// Classe con le funzioni del ilDelirante
class Delirio {
	//Variabile che conterrà i vari op
	var $op = array();
	//Variabile che conterrà i vari insulti
	var $insulti = array();
	//Variabile che blocca insulto personalizzato
	var $stop = true;
	//Settiamo le varie proprietà del bot
	function setVar( ) {
		$this->op = array_map('rtrim',file( 'op.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->insulti = array_map('rtrim',file( 'insulti.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	}
	//Saluto chi entra gentilmente
	function onjoin_greeting( &$irc, &$data ) {
		if( $data->nick == $irc->_nick ) {return;}
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Ciao '.$data->nick );
	}
	//Spengo il bot
	function shutdown( &$irc, &$data ) {
		if( in_array($data->nick, $this->op) ) {
			$irc->quit('Addio mondo crudele!');
		} else {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Chi ti credi di essere per darmi questi comandi?????');
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
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'dont kick me... please' );
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
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Fottiti '.implode('',$poggio) );
			}
	}
	//Stampa la bio dell'utente o del parametro passato
	function whoami( &$irc, &$data ) {
		global $bio;
			if( isset( $data->messageex[1] ) && isset($bio[$data->messageex[1]]['bio'])) {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $data->messageex[1].': '.$bio[$data->messageex[1]]['bio'] );
			} elseif(!isset($data->messageex[1]) && isset($bio[$data->nick]['bio'])) {
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $data->nick.': '.$bio[$data->nick]['bio'] );
			}else{
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Utente non inserito nel sistema. Tentativo di intrusione rilevato!' );
			}
	}
	//Lista dei comandi
	function help( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Comandi: !saluta, !help, !whoami, !versione, !github, !who, !ls, !insulta, !paste, !google, !deb, !rpm' );
	}
	//Versione
	function versione( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Sono cavoli miei... 0.0.8' );
	}
	//Link su Github del bot
	function github( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Sorgenti: https://github.com/Mte90/Delirante' );
	}
	//Utenti nel database
	function who( &$irc, &$data ) {
		global $bio_tot;
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Utenti nel database: '.implode(', ', $bio_tot) );
	}
	//Insulta
	function insulta( &$irc, &$data ) {
		if( isset( $data->messageex[1] )) {
			if($data->messageex[1]=='-c') {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, count($this->insulti).' insulti nel sistema');
			}elseif(is_numeric($data->messageex[1])&&$data->messageex[1]<count($this->insulti)&&isset($data->messageex[2])) {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $this->insulti[$data->messageex[1]].' '.$data->messageex[2]);
			}elseif(is_numeric($data->messageex[1])&&$data->messageex[1]<count($this->insulti)) {
				$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $this->insulti[$data->messageex[1]]);
			} else {
				if(in_array($data->messageex[1], $irc->_updateIrcUser($data))) {
					$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
				}
			}
		} else {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, '-c Mostra il numero di insulti, !insulta NUMERO[non obbligatorio] NICK');
		}
	}
	//Insulto personalizzato a citazione
	function insulto( &$irc, &$data ) {
	if(rand(0, 20)==1){$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, "Mi servono molti insulti personalizzati!");}
		if($this->stop && $data->messageex[0][0]!='!'){
		$messaggio=implode(' ',$data->messageex);
		global $bio;
			foreach($irc->_updateIrcUser($data) as $item){
				if(strstr($messaggio,$item)){
					if($item=='ilDelirante'){
						$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$data->nick);
					}elseif(isset($bio[$item]['insulto'][0])){
						$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$item);
					}
				}
			}
		}
	}
	//Cambia il nick attuale??
	function changeNick( $newnick, $priority = SMARTIRC_MEDIUM ) {
		$this->_send( 'NICK '.$newnick, $priority );
		$this->_nick = $newnick;
	}
	//Elenco Utenti
	function ls( &$irc, &$data ) {
		$nicklist=$irc->_updateIrcUser($data);
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Utenti nel sistema: '.substr(implode(', ', $nicklist), 0, -10));
	}
	//Disattiva/Attiva l'insulto personalizzato
	function stop( &$irc, &$data ) {
		if( in_array($data->nick, $this->op) ) {
			if($this->stop==false){$this->stop=true;}else{$this->stop=false;}
		} else {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'Chi ti credi di essere per darmi questi comandi?????');
		}
	}
	//Pastebin vari
	function paste( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'http://pastebin.com , http://paste.kde.org , http://nopaste.voric.com');
	}
	//Google
	function google( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'http://www.google.it/search?q='.str_replace("!google ","",implode(' ',$data->messageex)));
	}
	//DEB
	function deb( &$irc, &$data ) {
		if($data->messageex[1]=='-ubu') {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'http://packages.ubuntu.com/search?keywords='.$data->messageex[2]);
		}elseif(!isset($data->messageex[1])) {
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, '!deb -ubu(per usare Ubuntu altrimenti Debian)');
		}else{
			$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'http://packages.debian.org/search?keywords='.$data->messageex[1]);
		}
	}
	//RPM
	function rpm( &$irc, &$data ) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, 'http://software.opensuse.org/search?q='.$data->messageex[1].'&baseproject=ALL');
	}
	/*End the Bot-class*/
}
//Impostiamo e facciamo partire il bot
$bot = new Delirio( );
$bot->setVar( );
$irc = new Net_SmartIRC( );
//$irc->startBenchmark();
//$irc->setDebug( SMARTIRC_DEBUG_ALL );
//$irc->setBenchmark(TRUE);
$irc->setUseSockets( TRUE );
$irc->setUserSyncing( TRUE );
$irc->setChannelSyncing( TRUE );
//Configuriamo i vari comandi con le funzioni
$irc->connect( 'irc.freenode.org', 6667 );
$irc->registerActionhandler( SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting' );
$irc->registerActionhandler( SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'insulto' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!shutdown$', $bot, 'shutdown' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rm', $bot, 'ban' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!saluta', $bot, 'saluta' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!help$', $bot, 'help' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!who\s*$', $bot, 'who' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!whoami', $bot, 'whoami' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!versione$', $bot, 'versione' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!github$', $bot, 'github' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!insulta', $bot, 'insulta' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!ls', $bot, 'ls' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!stop', $bot, 'stop' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!paste', $bot, 'paste' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!google', $bot, 'google' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!deb', $bot, 'deb' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rpm', $bot, 'rpm' );

// nick , nome , realname , ident, boh
$irc->login( 'ilDelirante', 'ilDelirante'.'delirio', 8, 'delirio', '' );
$irc->join( $chan );
$irc->listen( );
$irc->disconnect( );
?>