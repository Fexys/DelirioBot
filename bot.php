<?php
//Includo la classe SmartIRC che è presente nei repo di Debian ma è documentata da cani
include_once('SmartIRC.php');
include_once('SmartIRC/irccommands.php');
include_once('SmartIRC/messagehandler.php'); //queste funzioni sono extra ma non funzionano

//Includo il file con le bio
include('Database/bio.php');

//Variabile che contiene il nome del chan
$chan = '#DeliriNotturni';
//$chan = '#DeliriNotturni2';

//Classe con le funzioni del ilDelirante
class Delirio
{
	//Versione
	var $version = '0.0.24';

	//Variabili liste
	var $insulti = array();
	var $morte = array();
	var $filtro = array();
	
	//Variabile che blocca insulto personalizzato
	var $stop = false;
	
	//Settiamo le varie proprietà del bot
	function setVar()
	{
		$this->insulti = array_map('rtrim', file('Database/insulti.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
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

		global $bio;
		$present = 'Non sei presente nel nostro sistema!! Insultatelo, nao!';

		if (isset($bio[$data->nick])) {
			$present = 'Puoi prendere la birra gratis dal frigobar.';
			if (count($bio[$data->nick]['insulto']) < 3){
				$present = 'hai meno di 4 insulti personali, per te niente birra solo insulti.';
			}
		}

		$this->scrivi_messaggio($irc, $data, 'Ciao '.$data->nick.' '.$present);
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
			global $bio;
			if (isset($data->messageex[1]) && isset($bio[$data->messageex[1]]['bio'])) {
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].': '.$bio[$data->messageex[1]]['bio']);
			} elseif (!isset($data->messageex[1]) && isset($bio[$data->nick]['bio'])) {
				$this->scrivi_messaggio($irc, $data, $data->nick.': '.$bio[$data->nick]['bio']);
			} else {
				$this->scrivi_messaggio($irc, $data, 'Utente non inserito nel database. Tentativo di intrusione rilevato!');
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
			$this->scrivi_messaggio($irc, $data, 'Comandi da cazzeggio: !saluta, !whoami, !who, !insulta, !dado, !inalbera, !noi, !birra, !muori, !gaio, !amore, !nutella');
			$this->scrivi_messaggio($irc, $data, 'Tool: !help, !versione, !github, !blog, !ls, !paste, !google, !deb, !rpm, !pkg');
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
		$this->scrivi_messaggio($irc, $data, 'Sorgenti: http://mte90.github.com/Delirante/ - Fate ticket a volontà!');
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
		//da eliminare?! mi sembra solo una rottura di cabasisi
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

			$str = preg_match("/\-lang:([^ ]*)/", $termine, $lang);
			if (isset($lang[1])) {
				$adds_vars .= '&hl='.$lang[1];
				$termine = trim(preg_replace("/\-lang([^ ]*)/", '', $termine));
			}

			$str = preg_match("/\-([^ ]*) (.*)/", $termine, $output);
			if (isset($output[1])) {
				if ($output[1] == 'image')
					$adds_vars .= '&tbm=isch';

				if ($output[1] == 'video')
					$adds_vars .= '&tbm=vid';

				$termine = str_replace(' ', '+', trim($output[2]));
			} else {
				$termine = str_replace(' ', '+', $termine);
			}

			if (!preg_match('/('.$filtrot.')+/i', $termine) && isset($data->messageex[2])) {
				$this->scrivi_messaggio($irc, $data,'https://www.google.it/search?q='.$termine.$adds_vars);
			} elseif (!isset($data->messageex[1])) {
				$this->scrivi_messaggio($irc, $data, '!google seguito da uno spazio e dalla frase/parola che vuoi cercare');
			} else {
				$this->scrivi_messaggio($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
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
}

//Impostiamo e facciamo partire il bot
$bot = new Delirio( );
$bot->setVar();
$irc = new Net_SmartIRC( );
$irc->startBenchmark();
$irc->setDebug(SMARTIRC_DEBUG_ALL);
$irc->setBenchmark(TRUE);
$irc->setUseSockets(TRUE);
$irc->setUserSyncing(TRUE);
$irc->setChannelSyncing(TRUE);
$irc->setSenddelay(500);

//Configuriamo i vari comandi con le funzioni
$irc->connect('irc.freenode.org', 6667);
$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting');
$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'insulto_');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!*', $bot, 'check');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kill$', $bot, 'kill');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rm', $bot, 'ban');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!refresh', $bot, 'refresh');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!saluta', $bot, 'saluta');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!help$', $bot, 'help');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!who\s*$', $bot, 'who');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!whoami', $bot, 'whoami');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!versione$', $bot, 'versione');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!github$', $bot, 'github');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!insulta', $bot, 'insulta');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ls', $bot, 'ls');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!stop', $bot, 'stop');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $bot, 'paste');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $bot, 'google');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deb', $bot, 'deb');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rpm', $bot, 'rpm');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $bot, 'pkg');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $bot, 'dado');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!inalbera', $bot, 'inalbera');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!battezza', $bot, 'battezza');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!noi', $bot, 'noi');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!birra', $bot, 'birra');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!muori', $bot, 'muori');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!gaio', $bot, 'gaio');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!tumblr', $bot, 'tumblr');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!amore', $bot, 'amore');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nutella', $bot, 'nutella');

//Nickname, Nome , RealName, Ident, Password
$irc->login('ilDelirante', 'ilDelirante'.'delirio', 8, 'delirio', '');
$irc->join($chan);
$irc->listen();
$irc->disconnect();

?>