<?php

include('SmartIRC.php');

include('Database/bio.php');

class DelirioBot
{
	var $config = array();
	var $users = array();
	var $manual = array();
	var $insulti = array();
	var $morte = array();
	var $filtro = array();
	var $stop = FALSE;

	function DelirioBot(&$irc) {
		$this->run($irc);
	}

	function run(&$irc)
	{
		require_once('config.php');

		//Set variabili di connessione
		foreach ($config as $key => $value) {
			$this->server[$key] = $value;
		}

		//Set variabili Bot
		foreach ($config['bot'] as $key => $value) {
			$this->config[$key] = $value;
		}
		$this->set_vars();

		//Setup di SmartIRC
		$irc->setDebug(SMARTIRC_DEBUG_ALL);
		//$irc->setLogfile(LOG_DIR . 'debug_bot.log');
		//$irc->setLogdestination(SMARTIRC_FILE);
		$irc->setUseSockets(TRUE);
		$irc->setUserSyncing(TRUE);
		$irc->setChannelSyncing(TRUE);
		$irc->setAutoRetry(TRUE);
		$irc->setAutoReconnect(TRUE);
		$irc->setReceiveTimeout(6000);
		$irc->setTransmitTimeout(6000);
		$irc->setCtcpVersion($this->config['nickname'] . ' [ver ' . VERSION . ']');
		$irc->setSendDelay(500);

		//Lista comandi
		$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $this, 'join_greeting');
		$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $this, 'kick_response');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'mention_insult');
		//$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/alphacode/i', $this, 'alpha_kick');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!info$', $this, 'info');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!tumblr', $this, 'tumblr');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!help$', $this, 'help');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!man', $this, 'man');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!saluta', $this, 'give_greeting');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ls$', $this, 'online_users_list');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!who$', $this, 'who');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!whoami', $this, 'whoami');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!twitter', $this, 'twitter');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!noi', $this, 'noi');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!insulta', $this, 'insulta');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!inalbera', $this, 'inalbera');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!muori', $this, 'muori');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!supercazzola', $this, 'supercazzola');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!calcio', $this, 'calcio');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!gaio$', $this, 'gaio');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!amore', $this, 'amore');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!birra', $this, 'birra');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nutella', $this, 'nutella');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $this, 'google_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!yt', $this, 'youtube_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!porn', $this, 'porn_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!anime', $this, 'anime_serch');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $this, 'packages_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!translate', $this, 'translate');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $this, 'paste');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $this, 'dado');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $this, 'nick');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!set', $this, 'settings');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!refresh$', $this, 'refresh');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!join', $this, 'join');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!part', $this, 'part');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!rejoin', $this, 'rejoin');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!op', $this, 'op');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!deop', $this, 'deop');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!voice', $this, 'voice');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!devoice', $this, 'devoice');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kick', $this, 'kick');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!roulette$', $this, 'roulette');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!clear$', $this, 'clear');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!mute', $this, 'mute');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!unmute', $this, 'unmute');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ban', $this, 'ban');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!unban', $this, 'unban');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!battezza', $this, 'battezza');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!kill$', $this, 'disconnect');

		$irc->connect($this->server['server'], $this->server['port']);
		$irc->login($this->config['nickname'], $this->config['realname'], $this->config['usermode'], $this->config['username'], $this->config['password']);
		$irc->join($this->server['channel']);
		$irc->listen();
		$irc->disconnect();
	}

	/**
	 * Settaggio delle variabili del database
	 *
	 * @return	void
	 */
	function set_vars()
	{
		$users_db_path = DATABASE_DIR . 'users.json';
		$manual_db_path = DATABASE_DIR . 'manual.json';
		$insulti_db_path = DATABASE_DIR . 'insulti.php';
		$supercazzole_db_path = DATABASE_DIR . 'supercazzole.php';
		$morte_db_path = DATABASE_DIR . 'morte.php';
		$filtro_db_path = DATABASE_DIR . 'filtro.php';

		$data_users = file_get_contents($users_db_path);
		$data_manual = file_get_contents($manual_db_path);

		$this->users = json_decode($data_users, true);
		$this->manual = json_decode($data_manual, true);
		$this->insulti = array_map('rtrim', file($insulti_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->supercazzole = array_map('rtrim', file($supercazzole_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->morte = array_map('rtrim', file($morte_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->filtro = array_map('rtrim', file($filtro_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	}

	/**
	 * Distruzione delle variabili del database
	 *
	 * @return	void
	 */
	function unset_vars()
	{
		unset($this->users, $this->insulti, $this->supercazzole, $this->morte, $this->filtro);
	}

	/**
	 * Funzione con la quale il Bot scrive sul chan.
	 *
	 * @param	string
	 * @return	string
	 */
	function talk(&$irc, &$data, $message)
	{
		$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
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
			$irc->_send('MODE ' . $channel . ' ' . $newmode, $priority);
		} else {
			$irc->_send('MODE ' . $channel, $priority);
		}
	}

	/**
	 * Trova nel database una corrispondenza totale o parziale per il nickname richiesto
	 * restituendo il risultato vero/falso della ricerca ed il nickname richiesto.
	 *
	 * @param	string
	 * @return	array
	 */
	function is_user_exists($nickname)
	{
		$result = array();
		$users_list = array_keys($this->users);

		foreach ($users_list as $value) {
			if (preg_match("/$value/i", $nickname)) {
				$result['condition'] = TRUE;
				$result['user'] = $value;
				break;
			} else {
				$result['condition'] = FALSE;
			}
		}

		return $result;
	}

	/**
	 * WIP
	 */
	function search_alias($nickname)
	{
		$users_list = array_keys($this->users);

		foreach ($users_list as $value) {
			for ($i=0; $i < count($this->users[$value]['alias']); $i++) { 
				echo $this->users[$value]['alias'][$i];
			}
		}
	}

	/**
	 * Rimuove determinati elementi dall'array tramite il valore richiesto.
	 *
	 * @param	array
	 * @param	string
	 * @return	array
	 */
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
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Accoglie con un saluto l'utente che è appena entrato nel canale
	 * fornendo l'informazione se l'utente è presente nel database o meno.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function join_greeting(&$irc, &$data)
	{
		if ($data->nick == $irc->_nick) {
			return;
		}

		$result = $this->is_user_exists($data->nick);

		$condition = $result['condition'];
		$nickname = $result['user'];

		if ($condition) {
			$this->talk($irc, $data, $this->users[$nickname]['saluto']);

			//$present = 'puoi prendere la birra gratis dal frigobar.';
		} else {
			$this->talk($irc, $data, 'Ciao ' . $data->nick . ', non sei presente nel nostro database. Insultatelo, nao!');
		}
	}

	/**
	 * 
	 *
	 * @param	string
	 * @return	string
	 */
	function kick_response(&$irc, &$data)
	{
		//If bot is kicked
		//if ($data->nick == $irc->_nick) {
		//	$irc->join(array($chan));
			$this->talk($irc, $data, '-1 :-P');
		//	return;
		//}
	}

	/**
	 * Verifica funzione esistente.
	 *
	 * @param	string
	 * @return	string
	 */
	function check_command(&$irc, &$data)
	{
		if (isset($data->messageex[0]) && $data->messageex[0][0] == '!' && !in_array(str_replace('!', '', $data->messageex[0]), get_class_methods($this)) && !$this->flood($data)) {
			$poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ');
			$this->talk($irc, $data, 'Non conosco questo comando '.$data->nick.', quindi sarai calciorotato da Chuck Norris, smontato da McGyver e insultato da '.$poggio[array_rand($poggio,1)]);
		}
	}

	/**
	 * Insulto personalizzato a citazione.
	 *
	 * @param	string
	 * @return	string
	 */
	function mention_insult(&$irc, &$data)
	{
		if (rand(0, 24) == 1 && $this->stop) {
			$this->talk($irc, $data, 'Sarete calciorotati il prima possibile se non mi date degli insulti personali!');
		}

		if ($this->stop && $data->messageex[0][0] != '!') {
			$messaggio = implode(' ', $data->messageex);
			global $bio;

			foreach ($irc->_updateIrcUser($data) as $item) {
				if (strstr($messaggio,$item)) {
					if ($item == $this->config['nickname']) {
						$this->talk($irc, $data, $bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$data->nick);
					} elseif (isset($bio[$item]['insulto'][0])) {
						$this->talk($irc, $data, $bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$item);
					}
				}
			}
		}
	}

	/**
	 * Versione del Bot.
	 *
	 * @param	string
	 * @return	string
	 */
	function info(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->talk($irc, $data, $this->config['nickname'] . ' [ver ' . VERSION . ']. Sorgenti, idee e segnalazioni bug su https://mte90.github.com/Delirante/');
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
		$this->talk($irc, $data, 'Casa dolce casa... http://delirinotturni.tumblr.com/');
	}

	/**
	 * Lista dei comandi disponibili.
	 *
	 * @return	message
	 */
	function help(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->talk($irc, $data, '[Comandi] !info, !tumblr, !man, !saluta, !ls, !who, !whoami, !noi, !insulta, !inalbera, !muori, !supercazzola, !calcio, !gaio, !amore, !birra, !nutella');
			$this->talk($irc, $data, '[Strumenti] !google, !yt, !porn, !anime, !pkg, !translate, !paste');
			$this->talk($irc, $data, '[Giochi] !dado');
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
				$this->talk($irc, $data, '[Descrizione] ' . $data->messageex[1] . ' -- ' . $this->manual[$data->messageex[1]]['description']);
				$this->talk($irc, $data, '[Sinossi] ' . $this->manual[$data->messageex[1]]['synopsis']);
			} else {
				$this->talk($irc, $data, '!man <query>');
			}
		}
	}

	/**
	 * Saluta l'utente richiesto.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function give_greeting(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
					$this->talk($irc, $data, 'Fottiti '.$data->messageex[1]);
				} elseif ((in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname'])) {
					$this->talk($irc, $data, 'Fottiti '.$data->nick);
				}
			}
		}
	}

	/**
	 * Elenco utenti connessi nel canale.
	 *
	 * @param	string
	 * @return	string
	 */
	function online_users_list(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$nicklist = $this->remove_item_by_value($irc->_updateIrcUser($data), 'ChanServ');
			$this->talk($irc, $data, count($nicklist).' deliranti connessi: '.implode(', ', $nicklist));
		}
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
			$this->talk($irc, $data, count(array_keys($this->users)).' utenti nel database: '.implode(', ', array_keys($this->users)));
		}
	}

	/**
	 *
	 *
	 *
	 *
	 */
	function whoami(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$result = $this->is_user_exists($data->messageex[1]);
			} else {
				$result = $this->is_user_exists($data->nick);
			}

			$condition = $result['condition'];
			$nickname = $result['user'];

			if ($condition) {
				$this->talk($irc, $data, 'Biografia di ' . $nickname . ' -- ' . $this->users[$nickname]['bio']);
			} else {
				$this->talk($irc, $data, 'L\'utente non inserito nel database. Tentativo di intrusione rilevato!');
			}
		}
	}

	function twitter(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$result = $this->is_user_exists($data->messageex[1]);
			} else {
				$result = $this->is_user_exists($data->nick);
			}

			$condition = $result['condition'];
			$nickname = $result['user'];

			if ($condition) {
				$this->talk($irc, $data, 'Twitter di ' . $nickname . ' -- ' . $this->users[$nickname]['twitter']);
			} else {
				$this->talk($irc, $data, 'L\'utente non inserito nel database. Tentativo di intrusione rilevato!');
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
			if (isset($data->messageex[1]) && in_array($data->messageex[1], $irc->_updateIrcUser($data))) {
				$this->talk($irc, $data, $data->messageex[1] . ' è ora uno di noi, uno di noi, uno di noi, uno di noi, un delirante come noi!');
			} else {
				$this->talk($irc, $data, $data->nick . ', chi cacchio è ' . $data->messageex[1] . '? Tua suocera o la tua mano destra?');
			}
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
				$total_insults = count($this->insulti);
				$insult_rand = $this->insulti[array_rand($this->insulti)];

				if (is_numeric($data->messageex[1]))
					$n = str_replace('-', '', (int)$data->messageex[1]);

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_insults . ' insulti presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && ($n < $total_insults) && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($this->insulti[$n]));
				} elseif (is_numeric($data->messageex[1]) && $n < $total_insults) {
					$this->talk($irc, $data, $this->insulti[$n]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($insult_rand));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo divino Bot!');
					}
				}
			} else {
				$this->talk($irc, $data, '');
			}
		}
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
				if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
					for ($i=0; $i < 5; $i++) {
						$insult_rand = $this->insulti[array_rand($this->insulti)];
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($insult_rand));
					}
				} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
					$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo divino Bot!');
				} else {
				}
			}
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
			if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
				$this->talk($irc, $data, $data->messageex[1].' '.$this->morte[array_rand($this->morte)]);
			} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
				$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo divino Bot!');
			}
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
					$this->talk($irc, $data, count($this->supercazzole).' supercazzole nel sistema');
				} elseif (is_numeric($data->messageex[1]) && str_replace('-', '', (int)$data->messageex[1]) < count($this->supercazzole) && isset($data->messageex[2])) {
					$this->talk($irc, $data, $data->messageex[2].' '.$this->supercazzole[str_replace('-','',(int)$data->messageex[1])]);
				} elseif (is_numeric($data->messageex[1]) && str_replace('-', '', (int)$data->messageex[1]) < count($this->supercazzole)) {
					$this->talk($irc, $data, $this->supercazzole[str_replace('-', '' ,(int)$data->messageex[1])]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1].' '.$this->supercazzole[array_rand($this->supercazzole)]);
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick.' '.$this->supercazzole[array_rand($this->supercazzole)]);
					}
				}
			} else {
				$this->talk($irc, $data, '!supercazzola [n | -c] <username>');
			}
		}
	}

	/**
	 * Calcio.
	 *
	 * @param	string
	 * @return	string
	 */
	function calcio(&$irc, &$data)
	{
		$calci = rand(0, 100);
		switch ($calci) {
			case '1':
				$this->talk($irc, $data, $data->nick . ', sarai calciorotato solamente per ' . $calci . ' volta!');
				break;
			
			case '100':
				$this->talk($irc, $data, $data->nick . ', sarai calciorotato per ben ' . $calci . ' volte!');
				break;

			default:
				$this->talk($irc, $data, $data->nick . ', sarai calciorotato ' . $calci . ' volte!');
				break;
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
			$poggio = $poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ');
			$this->talk($irc, $data, 'Il gay del momento è '.$poggio[array_rand($poggio,1)]);
		}
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
			$poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), $data->nick);
			$this->talk($irc, $data, $data->nick.' Lovva '.$poggio[array_rand($poggio,1)].' <3');
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
			$poggio = $this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']);
			$this->talk($irc, $data, 'Una bella damigiana di birra per tutti offerta da '.$data->nick.'!');
			$alcool = array('San Crispino','Tavernello','Olio Cuore','Estathé');
			$this->talk($irc, $data, 'Per '.$poggio[array_rand($poggio,1)].' solo '.$alcool[array_rand($alcool)].' u.u');
		} elseif (isset($data->messageex[1])) {
			$this->talk($irc, $data, $data->messageex[1]. ' Eccoti una bella birra fredda marchio Delirio offerta da '.$data->nick.'!');
			$this->talk($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
		} elseif (!isset($bio[$data->nick])) {
			$this->talk($irc, $data, 'Tu vorresti la nostra birra?? Non sei nel database brutta pustola.');
			$this->talk($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
			$this->talk($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
		} elseif (isset($bio[$data->nick]['insulto']) && count($bio[$data->nick]['insulto']) < 3){
			$this->talk($irc, $data, 'A te niente birra brutto stronzetto, senza insulti personali non vai da nessuna parte,');
			$this->talk($irc, $data, $data->nick.' '.$this->insulti[array_rand($this->insulti)]);
		} else {
			$this->talk($irc, $data, 'Eccoti una bella birra fredda marchio Delirio!');
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
			$this->talk($irc, $data, 'Tirate fuori i vostri cucchiai! Nutella Party ON');
		}
	}

	/**
	 * Ricerca su Google.
	 *
	 * @param	string
	 * @return	string
	 */
	function google_search(&$irc, &$data)
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
				$this->talk($irc, $data,'https://www.google.it/search?q='.$termine.$adds_vars);
			} elseif (!isset($data->messageex[1])) {
				$this->talk($irc, $data, '!google [-img | -vid | -l:it] <query>');
			} else {
				$this->talk($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * Ricerca YouTube.
	 *
	 * @param	string
	 * @return	string
	 */
	function youtube_search(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$filtrot = implode('|', $this->filtro);
			$termine = preg_replace('/[^a-zA-Z0-9\s]/', '', urldecode(str_replace('!yt ', '', implode(' ', $data->messageex))));
			$termine = str_replace(' ', '+', $termine);

			if (!preg_match('/('.$filtrot.')+/i', $termine) && isset($data->messageex[1])) {
				$this->talk($irc, $data, 'http://www.youtube.com/results?search_query='.$termine);
			} elseif (!isset($data->messageex[1])) {
					$this->talk($irc, $data, '!yt <query>');
			} else {
				$this->talk($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * Ricerca di materiale pornografico.
	 *
	 * @param	string
	 * @return	string
	 */
	function porn_search(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$termine = urldecode(str_replace('!porn ', '', implode(' ', $data->messageex)));
			$termine = trim(preg_replace("/\-([^ ]*)/", '', $termine));
			$termine = str_replace(' ', '+', $termine);

			if (isset($data->messageex[2])) {
				switch ($data->messageex[1]) {
					case '-yp':
						$this->talk($irc, $data, 'http://www.youporn.com/search/?query='.$termine.'&type=straight');
						break;

					case '-yj':
						$termine = str_replace('+', '-', $termine);
						$this->talk($irc, $data, 'http://www.youjizz.com/search/'.$termine.'-1.html');
						break;

					case '-t8':
						$this->talk($irc, $data, 'http://www.tube8.com/search.html?q='.$termine);
						break;

					case '-rt':
						$this->talk($irc, $data, 'http://www.redtube.com/?search='.$termine);
						break;

					case '-ph':
						$this->talk($irc, $data, 'http://www.pornhub.com/video/search?search='.$termine);
						break;

					case '-fk':
						$this->talk($irc, $data, 'http://www.fakku.net/manga.php?search='.$termine);
						break;

					case '-tg':
						$this->talk($irc, $data, 'http://tubegalore.com/search/?q='.$termine);
						break;

					case '-tm':
						$this->talk($irc, $data, 'http://www.tubemonsoon.com/videos?search='.$termine.'&btn=Search');
						break;

					default:
						$this->talk($irc, $data, 'http://findtubes.com/search/?q='.$termine);
						break;
				}
			} elseif (isset($data->messageex[1])) {
				$this->talk($irc, $data, 'http://findtubes.com/search/?q='.$termine);
			} else {
				$this->talk($irc, $data, '!porn [-yp -yj -t8 -rt -ph -fk -tg -tm] <query>');
			}
		}
	}

	/**
	 * Ricerca di anime e lista fansubber.
	 *
	 * @param	string
	 * @return	string
	 */
	function anime_search(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$termine = urldecode(str_replace('!anime ', '', implode(' ', $data->messageex)));
			$termine = trim(preg_replace("/\-([^ ]*)/", '', $termine));
			$termine = str_replace(' ', '+', $termine);

			if (isset($data->messageex[2])) {
				switch ($data->messageex[1]) {
					case '-mal':
						$this->talk($irc, $data, 'http://myanimelist.net/anime.php?q='.$termine);
						break;

					case '-ac':
						$this->talk($irc, $data, 'http://www.animeclick.it/anime.php?titolo='.$termine);
						break;

					case '-fansub':
						$this->talk($irc, $data, 'http://www.animeclick.it/ListaFansubs.php?keywords='.$termine.'&ftip=T');
						break;

					default:
						break;
				}
			} else {
				$this->talk($irc, $data, '!anime [-mal -ac -fansub] <query>');
			}
		}
	}

	/**
	 * Ricerca package.
	 *
	 * @param	string
	 * @return	string
	 */
	function packages_search(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$termine = urldecode($termine);
			$termine = str_replace('!pkg ', '', implode(' ', $data->messageex));
			$termine = preg_replace("/\-([^ ]*)/", '', $termine);
			$termine = trim($termine);
			$termine = str_replace(' ', '+', $termine);

			if (isset($data->messageex[2])) {
				switch ($data->messageex[1]) {
					case '-rpm':
						$this->talk($irc, $data, 'http://rpmfusion.org/RPM%20Fusion?action=fullsearch&value=' . $termine . '&titlesearch=Titoli');
						break;

					case '-deb':
						$this->talk($irc, $data, 'http://packages.debian.org/search?keywords=' . $termine);
						break;

					case '-ubu':
						$this->talk($irc, $data, 'http://packages.ubuntu.com/search?keywords=' . $termine);
						break;

					case '-arch':
						$this->talk($irc, $data, 'http://www.archlinux.org/packages/?q=' . $termine);
						break;

					case '-aur':
						$this->talk($irc, $data, 'https://aur.archlinux.org/packages.php?K=' . $termine);
						break;

					case '-suse':
						$this->talk($irc, $data, 'http://software.opensuse.org/search?q=' . $termine);
						break;

					default:
						$this->talk($irc, $data, '');
						break;
				}
			} else {
				$this->talk($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
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
				$this->talk($irc, $data, '!translate lang_soure|lang_destination <query>');
			} else {
				$this->talk($irc, $data,'http://translate.google.it/#'.$data->messageex[1].'|'.$termine);
			}	
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
		$this->talk($irc, $data, 'http://pastebin.com/ || http://paste.kde.org/ || http://nopaste.voric.com/');
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

	function settings(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			switch ($data->messageex[1]) {
				case '-antiflood':
					if ($data->messageex[2] == 1) {
						$this->talk($irc, $data, 'L\'antiflood è stato attivato.');
					} elseif ($data->messageex[2] == 0) {
						$this->talk($irc, $data, 'L\'antiflood è stato disattivato.');
					}
					break;

				case '-filtro':
					if ($data->messageex[2] == 1) {
						$this->talk($irc, $data, 'Il filtro è stato attivato.');
					} elseif ($data->messageex[2] == 0) {
						$this->talk($irc, $data, 'Il filtro è stato disattivato.');
					}
					break;
				
				case '-insulta':
					if ($data->messageex[2] == 1) {
						$this->stop = TRUE;
						$this->talk($irc, $data, 'Adesso vi insulto a tutti! MUAHAHAH!');
					} elseif ($data->messageex[2] == 0) {
						$this->stop = FALSE;
						$this->talk($irc, $data, 'Mi sono stufato, per ora, di insultarvi!');
					}
					break;

				default:
					# code...
					break;
			}
		} else {
			$this->talk($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}

	/**
	 * Aggiorna i file del database.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function refresh(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$this->unset_vars();
			$this->set_vars();
			$this->talk($irc, $data, 'Aggiornato e più stronzo di prima!');
		} else {
			$this->talk($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
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
	 * Voice.
	 * (Riservata agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function voice(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->voice($channel, $nickname);
			}
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
				$this->talk($irc, $data, 'Sintassi comando errata: !kick $nick ragione');
			}
		}
	}

	function roulette(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$nicklist = $this->remove_item_by_value($this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ'), $data->nick);

			$n = rand(0, count($nicklist));
			$irc->kick($data->channel, $nicklist[$n], 'La roulette ha scelto te! SBANG!');
		}
	}


	function clear(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$nicklist = $this->remove_item_by_value($this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ'), $data->nick);

			$irc->message($data->type, $data->channel, 'Il sistema è prossimo al colasso...');

			for ($i=0; $i < count($nicklist); $i++) { 
				$irc->kick($data->channel, $nicklist[$i], 'Kernel Panic!');
			}
		}
	}

	/**
	 * Imposta il mode +q all'utente richiesto inibendo la possibilità di poter scrivere sul canale.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	message
	 */
	function mute(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->mode($channel, '+q ' . $nickname, SMARTIRC_MEDIUM);
				$irc->message($data->type, $data->channel, $nickname . ', da ora in poi non puoi più parlare.');
			} else {
				$irc->message($data->type, $data->nick, 'Parametro non valido.');
				$irc->message($data->type, $data->nick, 'Usa: !mute <nickname>');
			}
		}
	}

	/**
	 * Rimuove il mode +q all'utente richiesto restituendo la possibilità di poter scrivere sul canale.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	message
	 */
	function unmute(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->mode($channel, '+q ' . $nickname, SMARTIRC_MEDIUM);
				$irc->message($data->type, $data->channel, $nickname . ', ora puoi tornare a parlare.');
			} else {
				$irc->message($data->type, $data->nick, 'Parametro non valido.');
				$irc->message($data->type, $data->nick, 'Usa: !unmute <nickname>');
			}
		}
	}

	/**
	 * Effettua il ban sull'utente selezionato.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	message
	 */
	function ban(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->ban($channel, $hostmask);
			} else {
				$irc->message($data->type, $data->nick, 'Parametro non valido.');
				$irc->message($data->type, $data->nick, 'Usa: !ban <nickname>');
			}
		}
	}

	/**
	 * Rimuove il ban sull'utente selezionato.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	message
	 */
	function unban(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->unban($channel, $hostmask);
			} else {
				$irc->message($data->type, $data->nick, 'Parametro non valido.');
				$irc->message($data->type, $data->nick, 'Usa: !unban <nickname>');
			}
		}
	}

	/**
	 * Battezza il nuovo utente.
	 * (Riservato agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function battezza(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$this->talk($data->type, $data->channel, $data->messageex[1].', ti battezzo nel nome del channel, delle puppe e dello spirito perverso. A te insulto iniziandoti a questa comunità delirante. Vai in pace ed espandi il nostro credo.');
				$this->talk($data->type, $data->channel, $data->messageex[1].', adesso sei uno di noi e puoi insultare con fierezza, per la birra gratis devi fornirci la tua biografia ed almeno un insulto personale. Ora puoi andartene a fanculo <3');
			}
		}
	}

	/**
	 * Disconnette il Bot.
	 * (Riservato agli operatori)
	 *
	 * @param	string
	 * @return	string
	 */
	function disconnect(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$irc->quit('Addio mondo crudele!');
		} else {
			$this->talk($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}

	function alpha_kick(&$irc, &$data)
	{
		$irc->kick($data->channel, $data->nick, 'Alphaqui, alphalà!');
	}
}