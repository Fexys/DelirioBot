<?php
//Includo la classe SmartIRC che è presente nei repo di Debian ma è documentata da cani
include_once('SmartIRC.php');
include_once('SmartIRC/irccommands.php');
include_once('SmartIRC/messagehandler.php'); //queste funzioni sono extra ma non funzionano

//Includo il file con le bio
include('Database/bio.php');

//Classe con le funzioni del ilDelirante
class Delirio
{
	//Versione
	var $version = '0.0.25';

	//Variabili liste
	var $users = array();
	var $insulti = array();
	var $morte = array();
	var $filtro = array();
	
	//Variabile che blocca insulto personalizzato
	var $stop = false;
	
	//Settiamo le varie proprietà del bot
	function setVar()
	{
		$pathfile = 'Database/users.json';
		$data = file_get_contents($pathfile);
		$this->users = json_decode($data, true);

		$this->insulti = array_map('rtrim', file('Database/insulti.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->supercazzole = array_map('rtrim', file('Database/supercazzole.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->morte = array_map('rtrim', file('Database/morte.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->filtro = array_map('rtrim', file('Database/filtro.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	}

	//Rimuove elementi da array tramite il valore ***uso Interno***
	function remove_item_by_value($array, $val = '')
	{
		if (!in_array($val, $array))
			return $array;

		foreach ($array as $key => $value) {
			if ($value == $val)
				unset($array[$key]);
		}

		return $array;
	}

	/**
	 * Funzione con la quale il Bot scrive sul chan.
	 * (Uso interno)
	 *
	 * @param	string
	 * @return	string
	 */
	function scrivi_messaggio(&$irc, &$data, $message)
	{
		$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
	}

	/**
	 * AntiFlood.
	 *
	 * @param	string
	 * @return	boolean
	 */
	function flood($data)
	{
		global $bio;
		$poggiotempo=0;
		
		if (isset($bio[$data->nick]['time'])) {
			$poggiotempo = $bio[$data->nick]['time'];
		}

		$bio[$data->nick]['time'] = time();
		
		if ($bio[$data->nick]['time'] - $poggiotempo < 3) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Verifica funzione esistente.
	 *
	 * @param	string
	 * @return	string
	 */
	function check(&$irc, &$data)
	{
		if (isset($data->messageex[0]) && $data->messageex[0][0] == '!' && !in_array(str_replace('!', '', $data->messageex[0]), get_class_methods($this)) && !$this->flood($data)) {
			$poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), 'ilDelirante'), 'ChanServ');
			$this->scrivi_messaggio($irc, $data, 'Non conosco questo comando '.$data->nick.', quindi sarai calciorotato da Chuck Norris, smontato da McGyver e insultato da '.$poggio[array_rand($poggio,1)]);
		}
	}

	/**
	 * Disattiva/Attiva l'insulto personalizzato.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string/boolean
	 */
	function stop(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if ($this->stop == FALSE) {
				$this->stop = TRUE;
				$this->scrivi_messaggio($irc, $data, 'Adesso vi insulto a tutti! Muahahahahah!');
			} else {
				$this->stop = FALSE;
				$this->scrivi_messaggio($irc, $data, 'Mi sono stufato, per adesso, di insultarvi!');
			}
		} else {
			$this->scrivi_messaggio($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}
	
	/**
	 * Saluta (gentilmente) chi entra nel canale.
	 *
	 * @param	string
	 * @return	string
	 */
	function onjoin_greeting(&$irc, &$data)
	{
		if ($data->nick == $irc->_nick) {
			return;
		}

		$present = 'non sei presente nel nostro sistema. Insultatelo, nao!';

		if (isset($this->users[$data->nick])) {
			$present = 'puoi prendere la birra gratis dal frigobar.';
			if (count($this->users[$data->nick]['insulto']) < 3){
				$present = 'hai meno di 4 insulti personali, per te niente birra solo insulti.';
			}
		}

		$this->scrivi_messaggio($irc, $data, 'Ciao '.$data->nick.', '.$present);
	}

	/**
	 * Chiude il Bot.
	 *
	 * @param	string
	 * @return	string
	 */
	function kill(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$irc->quit('Addio mondo crudele!');
		} else {
			$this->scrivi_messaggio($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}

	/**
	 * Kick.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function kick(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1], $data->messageex[2])) {
				$nickname = $data->messageex[1];
				$reason = $data->messageex[2];
				$channel = $data->channel;
				$irc->kick( $channel, $nickname, $reason);
			} else {
				$this->scrivi_messaggio($irc, $data, 'Sintassi comando errata: !kick $nick ragione');
			}
		}
	}
	
	/**
	 * Se il Bot è stato kickato, rientra.
	 *
	 * @param	string
	 * @return	string
	 */
	function kick_response(&$irc, &$data)
	{
		//If bot is kicked
		if ($data->nick == $irc->_nick) {
			$irc->join(array($chan));
			$this->scrivi_messaggio($irc, $data, '-1 :-P');
			return;
		}
	}
	
	/**
	 * Modalità del chan.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function mode($channel, $newmode = NULL, $priority = SMARTIRC_MEDIUM)
	{
		if ($newmode !== NULL) {
			$irc->_send('MODE '.$channel.' '.$newmode, $priority);
		} else {
			$irc->_send('MODE '.$channel, $priority);
		}
	}

	/**
	 * Devoice.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function devoice(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->devoice($channel, $nickname);
			}
		}
	}

	/**
	 * Op.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function op(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->op($channel, $nickname);
			}
		}
	}

	/**
	 * Deop.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function deop(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->deop($channel, $nickname);
			}
		}
	}

	/**
	 * Join.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function join(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$channel = $data->messageex[1];
				$irc->join($channel);
			}
		}
	}

	/**
	 * Part.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function part(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if(isset($data->messageex[1])) {
				$channel = $data->messageex[1];
				$irc->part($channel);
			}
		}
	}

	/**
	 * Rejoin.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function rejoin(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$channel = $data->messageex[1];
				$irc->part($channel);
				$irc->join($channel);
			}
		}
	}

	/**
	 * Ban.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function ban(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->ban($channel, $hostmask);
			} else {
				$irc->message($data->type, $data->nick, 'Invalid Parameter');
				$irc->message($data->type, $data->nick, 'use: !ban $nick');
			}
		}
	}

	/**
	 * Aggiorna i file del database (insulti, morti, biografie).
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function refresh(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			include('Database/bio.php');
			$this->setVar();
			$this->scrivi_messaggio($irc, $data, 'Aggiornato e più stronzo di prima!');
		} else {
			$this->scrivi_messaggio($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}

	/**
	 * Nick.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function nick(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$newnick = $data->messageex[1];
				$channel = $data->channel;
				$irc->changeNick($newnick);
			}
		}
	}

	/**
	 * Saluta (molto, ma molto educatamente).
	 *
	 * @param	string
	 * @return	string
	 */
	function saluta(&$irc, &$data)
	{
		if (isset($data->messageex[1])) {
			if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != 'ilDelirante') {
				$this->scrivi_messaggio($irc, $data, 'Fottiti '.$data->messageex[1]);
			} elseif ((in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == 'ilDelirante')) {
				$this->scrivi_messaggio($irc, $data, 'Fottiti '.$data->nick);
			}
		}
	}

	/**
	 * Stampa la biografia dell'utente o del parametro passato.
	 *
	 * @param	string
	 * @return	string
	 */
	function whoami(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1]) && isset($this->users[$data->messageex[1]]['bio'])) {
				$this->scrivi_messaggio($irc, $data, 'Biografia di '.$data->messageex[1].' -- '.$this->users[$data->messageex[1]]['bio']);
			} elseif (!isset($data->messageex[1]) && isset($this->users[$data->nick]['bio'])) {
				$this->scrivi_messaggio($irc, $data, 'Biografia di '.$data->nick.' -- '.$this->users[$data->nick]['bio']);
			} else {
				$this->scrivi_messaggio($irc, $data, 'L\'utente non inserito nel database. Tentativo di intrusione rilevato!');
			}
		}
	}

	/**
	 * Lista dei comandi.
	 *
	 * @param	string
	 * @return	string
	 */
	function help(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->scrivi_messaggio($irc, $data, 'Comandi da cazzeggio: !saluta, !whoami, !who, !insulta, !dado, !inalbera, !noi, !birra, !muori, !gaio, !amore, !nutella, !supercazzola, !porn');
			$this->scrivi_messaggio($irc, $data, 'Tool: !help, !versione, !github, !tumblr, !ls, !paste, !google, !deb, !rpm, !pkg, !yt, !translate');
		}
	}

	/**
	 * Versione del Bot.
	 *
	 * @param	string
	 * @return	string
	 */
	function versione(&$irc, &$data)
	{
		$this->scrivi_messaggio($irc, $data, 'Sono cavoli miei... '.$this->version);
	}

	/**
	 * Link su Github del Bot.
	 *
	 * @param	string
	 * @return	string
	 */
	function github(&$irc, &$data)
	{
		$this->scrivi_messaggio($irc, $data, 'Sorgenti disponibili su https://mte90.github.com/Delirante/ - Fate ticket a volontà!');
		$this->scrivi_messaggio($irc, $data, 'Volendo c\'è il fork di quel cazzone di Fexys https://github.com/Fexys/Delirante');
	}

	/**
	 * Utenti nel database.
	 *
	 * @param	string
	 * @return	string
	 */
	function who(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			global $bio_tot;
			$this->scrivi_messaggio($irc, $data, count($bio_tot).' utenti nel database: '.implode(', ', $bio_tot));
		}
	}

	/**
	 * Insulta.
	 *
	 * @param	string
	 * @return	string
	 */
	function insulta(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				if ($data->messageex[1] == '-c') {
					$this->scrivi_messaggio($irc, $data, count($this->insulti).' insulti nel sistema');
				} elseif (is_numeric($data->messageex[1]) && str_replace('-', '', (int)$data->messageex[1]) < count($this->insulti) && isset($data->messageex[2])) {
					$this->scrivi_messaggio($irc, $data, $data->messageex[2].' '.$this->insulti[str_replace('-','',(int)$data->messageex[1])]);
				} elseif (is_numeric($data->messageex[1]) && str_replace('-', '', (int)$data->messageex[1]) < count($this->insulti)) {
					$this->scrivi_messaggio($irc, $data, $this->insulti[str_replace('-', '' ,(int)$data->messageex[1])]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != 'ilDelirante') {
						$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == 'ilDelirante') {
						$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
					}
				}
			} else {
				$this->scrivi_messaggio($irc, $data, '-c Mostra il numero di insulti, !insulta NUMERO [non obbligatorio] NICK');
			}
		}
	}

	/**
	 * Insulto personalizzato a citazione.
	 *
	 * @param	string
	 * @return	string
	 */
	function insulto_(&$irc, &$data)
	{
		if (rand(0, 24) == 1 && $this->stop) {
			$this->scrivi_messaggio($irc, $data, 'Sarete calciorotati il prima possibile se non mi date degli insulti personali!');
		}

		if ($this->stop && $data->messageex[0][0] != '!') {
			$messaggio = implode(' ', $data->messageex);
			global $bio;

			foreach ($irc->_updateIrcUser($data) as $item) {
				if (strstr($messaggio,$item)) {
					if ($item == 'ilDelirante') {
						$this->scrivi_messaggio($irc, $data, $bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$data->nick);
					} elseif (isset($bio[$item]['insulto'][0])) {
						$this->scrivi_messaggio($irc, $data, $bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$item);
					}
				}
			}
		}
	}

	/**
	 * Cambia il nick attuale.
	 *
	 * @param	string
	 * @return	string
	 */
	function changeNick($newnick, $priority = SMARTIRC_MEDIUM)
	{
		$this->_send('NICK '.$newnick, $priority);
		$this->_nick = $newnick;
	}

	/**
	 * Elenco utenti.
	 *
	 * @param	string
	 * @return	string
	 */
	function ls(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$nicklist = $this->remove_item_by_value($irc->_updateIrcUser($data), 'ChanServ');
			$this->scrivi_messaggio($irc, $data, count($nicklist).' utenti nel sistema: '.implode(', ', $nicklist));
		}
	}

	/**
	 * Pastebin vari.
	 *
	 * @param	string
	 * @return	string
	 */
	function paste(&$irc, &$data)
	{
		$this->scrivi_messaggio($irc, $data, 'http://pastebin.com/ || http://paste.kde.org/ || http://nopaste.voric.com/');
	}

	/**
	 * Ricerca su Google.
	 *
	 * @param	string
	 * @return	string
	 */
	function google(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$adds_vars = '';
			
			$filtrot = implode('|', $this->filtro);
			$termine = urldecode(str_replace('!google ', '', implode(' ', $data->messageex)));

			$str = preg_match("/\-l:([^ ]*)/", $termine, $lang);
			if (isset($lang[1])) {
				$adds_vars .= '&hl='.$lang[1];
				$termine = trim(preg_replace("/\-l([^ ]*)/", '', $termine));
			}

			$str = preg_match("/\-([^ ]*) (.*)/", $termine, $output);
			if (isset($output[1])) {
				if ($output[1] == 'img')
					$adds_vars .= '&tbm=isch';

				if ($output[1] == 'vid')
					$adds_vars .= '&tbm=vid';

				$termine = str_replace(' ', '+', trim($output[2]));
			} else {
				$termine = str_replace(' ', '+', $termine);
			}

			if (!preg_match('/('.$filtrot.')+/i', $termine) && isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data,'https://www.google.it/search?q='.$termine.$adds_vars);
			} elseif (!isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data, '!google [-img | -vid | -l:it] <query>');
			} else {
				$this->scrivi_messaggio($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * Ricerca YouTube.
	 *
	 * @param	string
	 * @return	string
	 */
	function yt(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$filtrot = implode('|', $this->filtro);
			$termine = preg_replace('/[^a-zA-Z0-9\s]/', '', urldecode(str_replace('!yt ', '', implode(' ', $data->messageex))));
			$termine = str_replace(' ', '+', $termine);

			if (!preg_match('/('.$filtrot.')+/i', $termine) && isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data, 'http://www.youtube.com/results?search_query='.$termine);
			} elseif (!isset($data->messageex[1])) {
					$this->scrivi_messaggio($irc, $data, '!yt <query>');
			} else {
				$this->scrivi_messaggio($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * Ricerca p0rn.
	 *
	 * @param	string
	 * @return	string
	 */
	function porn(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$termine = urldecode(str_replace('!porn ', '', implode(' ', $data->messageex)));
			$termine = trim(preg_replace("/\-([^ ]*)/", '', $termine));
			$termine = str_replace(' ', '+', $termine);

			if (isset($data->messageex[2])) {
				if ($data->messageex[1] == '-yp') {
					$this->scrivi_messaggio($irc, $data, 'http://www.youporn.com/search/?query='.$termine.'&type=straight');
				} elseif ($data->messageex[1] == '-yj') {
					$termine = str_replace('+', '-', $termine);
					$this->scrivi_messaggio($irc, $data, 'http://www.youjizz.com/search/'.$termine.'-1.html');
				} elseif ($data->messageex[1] == '-t8') {
					$this->scrivi_messaggio($irc, $data, 'http://www.tube8.com/search.html?q='.$termine);
				} elseif ($data->messageex[1] == '-rt') {
					$this->scrivi_messaggio($irc, $data, 'http://www.redtube.com/?search='.$termine);
				} elseif ($data->messageex[1] == '-ph') {
					$this->scrivi_messaggio($irc, $data, 'http://www.pornhub.com/video/search?search='.$termine);
				}
			} elseif (isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data, 'http://findtubes.com/search/?q='.$termine);
			} else {
				$this->scrivi_messaggio($irc, $data, '!porn [-yp -yj -t8 -rt -ph] <query>');
			}
		}
	}

	/**
	 * DEB.
	 *
	 * @param	string
	 * @return	string
	 */
	function deb(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$filtrot = implode('|', $this->filtro);
			$termine = preg_replace('/[^a-zA-Z0-9\s]/', '', urldecode(str_replace('!google ', '', implode(' ', $data->messageex))));
			
			if (!preg_match('/('.$filtrot.')+/i', $termine)) {
				if ($data->messageex[1] == '-ubu' && isset($data->messageex[2])) {
					$this->scrivi_messaggio($irc, $data, 'http://packages.ubuntu.com/search?keywords='.$data->messageex[2]);
				} elseif (!isset($data->messageex[1])) {
					$this->scrivi_messaggio($irc, $data, '!deb -ubu (per usare Ubuntu altrimenti Debian)');
				} else {
					$this->scrivi_messaggio($irc, $data, 'http://packages.debian.org/search?keywords='.$data->messageex[1]);
				}
			} else {
				$this->scrivi_messaggio($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * RPM.
	 *
	 * @param	string
	 * @return	string
	 */
	function rpm(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$filtrot = implode('|', $this->filtro);
			$termine = preg_replace('/[^a-zA-Z0-9\s]/', '', urldecode(str_replace('!google ', '', implode(' ', $data->messageex))));

			if (!preg_match('/('.$filtrot.')+/i', $termine)) {
				$this->scrivi_messaggio($irc, $data, 'http://software.opensuse.org/search?q='.$data->messageex[1].'&baseproject=ALL');
			} else {
				$this->scrivi_messaggio($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * PKG.
	 *
	 * @param	string
	 * @return	string
	 */
	function pkg(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$filtrot = implode('|', $this->filtro);
			$termine = preg_replace('/[^a-zA-Z0-9\s]/', '', urldecode(str_replace('!google ', '', implode(' ', $data->messageex))));
			
			if (!preg_match('/('.$filtrot.')+/i', $termine)) {
				if ($data->messageex[1] == '-aur' && isset($data->messageex[2])) {
					$this->scrivi_messaggio($irc, $data,' https://aur.archlinux.org/packages.php?O=0&K='.$data->messageex[2].'&do_Search=Go');
				} elseif (!isset($data->messageex[1])) {
					$this->scrivi_messaggio($irc, $data, '!pkg -aur (per usare i repo non ufficiali)');
				} else {
					$this->scrivi_messaggio($irc, $data, 'http://www.archlinux.org/packages/?sort=&q='.$data->messageex[1].'&maintainer=&last_update=&flagged=&limit=50');
				}
			} else {
				$this->scrivi_messaggio($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}
	
	/**
	 * Google Translate.
	 *
	 * @param	string
	 * @return	string
	 */
	function translate(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$termine = urldecode(str_replace('!translate ', '', implode(' ', $data->messageex)));
			$termine = trim(str_replace($data->messageex[1], '', $termine));
			$termine = str_replace(' ', '+', $termine);

			if (!isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data, '!translate lang_soure|lang_destination <query>');
			} else {
				$this->scrivi_messaggio($irc, $data,'http://translate.google.it/#'.$data->messageex[1].'|'.$termine);
			}	
		}
	}

	/**
	 * Dado.
	 *
	 * @param	string
	 * @return	string
	 */
	function dado(&$irc, &$data)
	{
		$this->scrivi_messaggio($irc, $data, 'Predi questi '.rand(1, 6).' calci '.$data->nick);
	}

	/**
	 * Inalbera.
	 *
	 * @param	string
	 * @return	string
	 */
	function inalbera(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (!empty($data->messageex[1])) {
				if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != 'ilDelirante') {
					$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
				} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == 'ilDelirante') {
					$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
					$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
				} else {
					$this->scrivi_messaggio($irc, $data, 'Chi cacchio è '.$data->messageex[1].'? Tua suocera o la tua mano destra, '.$data->nick.'?');
				}
			}
		}
	}

	/**
	 * Battezza.
	 * (Riservato agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function battezza(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].', ti battezzo nel nome del channel, delle puppe e dello spirito perverso. A te insulto iniziandoti a questa comunità delirante. Vai in pace ed espandi il nostro credo.');
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].', adesso puoi insultare con fierezza, per la birra gratis devi dare la biografia e insulti personali.');
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].', sei uno di noi adesso puoi andartene a fanculo <3');
			}
		}
	}

	/**
	 * Uno di noi.
	 *
	 * @param	string
	 * @return	string
	 */
	function noi(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$nickad = '';
			if (in_array($data->messageex[1], $irc->_updateIrcUser($data))) {
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].'! Uno di noi Uno di noi Uno di noi Uno di noi Uno di noi Uno di noi Uno di noi Uno di noi Uno di noi!');
			} else {
				$this->scrivi_messaggio($irc, $data, 'Chi cacchio è '.$data->messageex[1].' tua suocera o la tua mano destra '.$data->nick.'?');
			}
		}
	}

	/**
	 * Birra.
	 *
	 * @param	string
	 * @return	string
	 */
	function birra(&$irc, &$data)
	{
	global $bio;
		if (isset($data->messageex[1]) && $data->messageex[1] == 'party') {
			$poggio = $this->remove_item_by_value($irc->_updateIrcUser($data), 'ilDelirante');
			$this->scrivi_messaggio($irc, $data, 'Una bella damigiana di birra per tutti offerta da '.$data->nick.'!');
			$alcool = array('San Crispino','Tavernello','Olio Cuore','Estathé');
			$this->scrivi_messaggio($irc, $data, 'Per '.$poggio[array_rand($poggio,1)].' solo '.$alcool[array_rand($alcool)].' u.u');
		} elseif (isset($data->messageex[1])) {
			$this->scrivi_messaggio($irc, $data, $data->messageex[1]. ' Eccoti una bella birra fredda marchio Delirio offerta da '.$data->nick.'!');
			$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
		} elseif (!isset($bio[$data->nick])) {
			$this->scrivi_messaggio($irc, $data, 'Tu vorresti la nostra birra?? Non sei nel database brutta pustola.');
			$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
			$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
		} elseif (isset($bio[$data->nick]['insulto']) && count($bio[$data->nick]['insulto']) < 3){
			$this->scrivi_messaggio($irc, $data, 'A te niente birra brutto stronzetto, senza insulti personali non vai da nessuna parte,');
			$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
		} else {
			$this->scrivi_messaggio($irc, $data, 'Eccoti una bella birra fredda marchio Delirio!');
		}
	}

	/**
	 * Morte.
	 *
	 * @param	string
	 * @return	string
	 */
	function muori(&$irc, &$data)
	{
		if (!$this->flood($data)) {
		global $morte;
			if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != 'ilDelirante') {
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->morte[array_rand($this->morte)]);
			} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == 'ilDelirante') {
				$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->morte[array_rand($this->morte)]);
			}
		}
	}

	/**
	 * Gaio.
	 *
	 * @param	string
	 * @return	string
	 */
	function gaio(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$poggio = $poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), 'ilDelirante'), 'ChanServ');
			$this->scrivi_messaggio($irc, $data, 'Il gay del momento è '.$poggio[array_rand($poggio,1)]);
		}
	}

	/**
	 * Tumblr.
	 *
	 * @param	string
	 * @return	string
	 */
	function tumblr(&$irc, &$data)
	{
		$this->scrivi_messaggio($irc, $data, 'Casa dolce casa... http://delirinotturni.tumblr.com/');
	}

	/**
	 * Amore.
	 *
	 * @param	string
	 * @return	string
	 */
	function amore(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), 'ilDelirante'), $data->nick);
			$this->scrivi_messaggio($irc, $data, $data->nick.' Lovva '.$poggio[array_rand($poggio,1)].' <3');
		}
	}

	/**
	 * Nutella.
	 * Attiva il Nutella Party
	 *
	 * @param	string
	 * @return	string
	 */
	function nutella(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->scrivi_messaggio($irc, $data, 'Tirate fuori i vostri cucchiai! Nutella Party ON');
		}
	}

	/**
	 * Supercazzola.
	 *
	 * @param	string
	 * @return	string
	 */
	function supercazzola(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				if ($data->messageex[1] == '-c') {
					$this->scrivi_messaggio($irc, $data, count($this->supercazzole).' supercazzole nel sistema');
				} elseif (is_numeric($data->messageex[1]) && str_replace('-', '', (int)$data->messageex[1]) < count($this->supercazzole) && isset($data->messageex[2])) {
					$this->scrivi_messaggio($irc, $data, $data->messageex[2].' '.$this->supercazzole[str_replace('-','',(int)$data->messageex[1])]);
				} elseif (is_numeric($data->messageex[1]) && str_replace('-', '', (int)$data->messageex[1]) < count($this->supercazzole)) {
					$this->scrivi_messaggio($irc, $data, $this->supercazzole[str_replace('-', '' ,(int)$data->messageex[1])]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != 'ilDelirante') {
						$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->supercazzole[array_rand($this->supercazzole)]);
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == 'ilDelirante') {
						$this->scrivi_messaggio($irc, $data, $data->nick.' '.$this->supercazzole[array_rand($this->supercazzole)]);
					}
				}
			} else {
				$this->scrivi_messaggio($irc, $data, '!supercazzola [n | -c] <username>');
			}
		}
	}

	/**
	 * DDoS.
	 * Da rivedere con onion2x
	 *
	 * @param	string
	 * @return	string
	 */
	function ddos(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->mode($channel, '+q '.$nickname, SMARTIRC_MEDIUM);
			} else {
				$this->scrivi_messaggio($irc, $data, '!ddos <username>');
			}
			print_r($data->messageex);
		}
	}

	/**
	 * Manuale.
	 *
	 * @param	string
	 * @return	string
	 */
	function man(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				switch ($data->messageex[1]) {
					case 'saluta':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] saluta -- Saluta l\'utente scelto.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !saluta <nickname>');
						break;

					case 'who':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] who -- Mostra il totale della biografie contenute nel database.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !who');
						break;

					case 'whoami':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] whoami -- Mostra la biografia dell\'utente scelto se indiciato altrimenti mostra la propria.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !whoami <query>');
						break;
					
					case 'versione':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] versione -- Restituisce la versione attuale de ilDelirante.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !versione');
						break;

					case 'github':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] github -- Restituisce il link al repository de ilDelirante su GitHub.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !github');
						break;

					case 'tumblr':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] tumblr -- Restituisce il link al tumblr con i migliori log di #DeliriNotturni.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !tumblr');
						break;

					case 'paste':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] paste -- Restituisce il link ai migliori servizi di one paste online.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !paste');
						break;
					
					case 'google':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] google -- Effettua la ricerca su Google.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !google [-img | -vid | -l:it] <query>');
						break;
					
					case 'yt':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] yt -- Effettua la ricerca su YouTube.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !yt <query>');
						break;
					
					case 'translate':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] translate -- Restituisce il link alla traduzione di una determinata parola/frase.');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !translate lang_soure|lang_destination <query>');
						break;
					
					case 'porn':
						$this->scrivi_messaggio($irc, $data, '[Descrizione] porn -- Effettua la ricerca su vari siti pornografici (YouPorn, YouJizz, Tube8, RedTube, PornHub).');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] !porn [-yp | -yj | -t8 | -rt | -ph] <query>');
						break;
					
					case 'value':
						$this->scrivi_messaggio($irc, $data, '[Descrizione]  -- .');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] ! <>');
						break;
					
					case 'value':
						$this->scrivi_messaggio($irc, $data, '[Descrizione]  -- .');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] ! <>');
						break;
					
					case 'value':
						$this->scrivi_messaggio($irc, $data, '[Descrizione]  -- .');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] ! <>');
						break;
					
					case 'value':
						$this->scrivi_messaggio($irc, $data, '[Descrizione]  -- .');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] ! <>');
						break;
					
					case 'value':
						$this->scrivi_messaggio($irc, $data, '[Descrizione]  -- .');
						$this->scrivi_messaggio($irc, $data, '[Sinossi] ! <>');
						break;
					
					default:
						# code...
						break;
				}
			} else {
				$this->scrivi_messaggio($irc, $data, '!man <query>');
			}
		}
	}
}