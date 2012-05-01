<?php

//Libreria di SmartIRC
include('SmartIRC.php');

class DelirioBot
{
	//Inizializzazione variabili di configurazione
	var $config = array();
	var $server = array();

	//Inizializzazione variabili database
	var $users = array();
	var $manual = array();
	var $insulti = array();
	var $deaths = array();
	var $filtro = array();

	//Inizializzazione variabili di impostazione
	var $stop = FALSE;
	var $antiflood = TRUE;

	//Varie
	var $flooders = array();

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
		$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '.*', $this, 'kick_rejoin');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'mention_insult');
		//$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/alphacode/i', $this, 'word_kick');
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
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!sex', $this, 'sex');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $this, 'google_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!yt', $this, 'youtube_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!porn', $this, 'porn_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!anime', $this, 'anime_serch');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $this, 'packages_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!translate', $this, 'translate');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $this, 'paste');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $this, 'dado');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!set', $this, 'settings');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!refresh$', $this, 'refresh');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!nick', $this, 'nick');
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
	 * Settaggio delle variabili del database.
	 *
	 * @return	void
	 */
	function set_vars()
	{
		$users_db_path = DATABASE_DIR . 'users.json';
		$manual_db_path = DATABASE_DIR . 'manual.json';
		$insulti_db_path = DATABASE_DIR . 'insulti.php';
		$supercazzole_db_path = DATABASE_DIR . 'supercazzole.php';
		$deaths_db_path = DATABASE_DIR . 'deaths.php';
		$filtro_db_path = DATABASE_DIR . 'filtro.php';

		$data_users = file_get_contents($users_db_path);
		$data_manual = file_get_contents($manual_db_path);

		$this->users = json_decode($data_users, true);
		$this->manual = json_decode($data_manual, true);
		$this->insulti = array_map('rtrim', file($insulti_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->supercazzole = array_map('rtrim', file($supercazzole_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->deaths = array_map('rtrim', file($deaths_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->filtro = array_map('rtrim', file($filtro_db_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	}

	/**
	 * Distruzione delle variabili del database.
	 *
	 * @return	void
	 */
	function unset_vars()
	{
		unset($this->users, $this->insulti, $this->supercazzole, $this->deaths, $this->filtro);
	}

	/**
	 * Funzione con la quale il Bot scrive sul chan.
	 * $irc->message($data->type, $data->channel, 'Message.');
	 *
	 * @param	string
	 * @return	string
	 */
	function talk(&$irc, &$data, $message)
	{
		$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
	}

	/**
	 * Funzione con la quale il Bot scrive in privato all'utente scelto.
	 * $irc->message($data->type, $data->nick, 'Message.');
	 *
	 * @param	string
	 * @return	string
	 */
	function pvt_talk(&$irc, $to, $message)
	{
		$irc->message(SMARTIRC_TYPE_QUERY, $to, $message);
	}
	
	/**
	 * Modalità del chan.
	 *
	 * @param	string
	 * @return	string
	 */
	/*function mode($channel, $newmode = NULL, $priority = SMARTIRC_MEDIUM)
	{
		if ($newmode !== NULL) {
			$irc->_send('MODE ' . $channel . ' ' . $newmode, $priority);
		} else {
			$irc->_send('MODE ' . $channel, $priority);
		}
	}*/

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
	 * A seconda della variabile richiesta, restituisce un array contenente un'ASCII Art.
	 *
	 * @param	string
	 * @return	array
	 */
	function ascii_art($draw)
	{
		switch ($draw) {
		 	case 'beer':
				$draw = array(
		 			'      _.._..,_,_',
					'     (          )',
					'      ]~,"-.-~~[',
					'    .=])\' (;  ([',
					'    | ]:: \'    [',
					'    \'=]): .)  ([',
					'      |:: \'    |',
					'       ~~----~~'
				);
		 		break;

		 	case 'penis':
		 		$draw = array(
					'      ___',
					'     //  7',
					'    (_,_/\\',
					'     \    \\',
					'      \    \\',
					'      _\    \__',
					'     (   \     )',
					'      \___\___/',
		 			);
		 		break;

			default:
		 		break;
		}

		return $draw;
	}

	/**
	 * Se attivato imposta un tempo massimo di 3 secondi tra l'esecuzione di un comando e l'altro.
	 *
	 * @param	int
	 * @return	boolean
	 */
	function flood($data)
	{
		if (!$this->antiflood)
			return FALSE;

		$time = 0;

		if (isset($this->flooders[$data->nick]['time'])) {
			$time = $this->flooders[$data->nick]['time'];
		}

		$this->flooders[$data->nick]['time'] = time();

		if (($this->flooders[$data->nick]['time'] - $time) < 3) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Accoglie con un saluto l'utente che è appena entrato nel canale
	 * fornendo l'informazione se l'utente è presente nel database o meno.
	 *
	 * @param	event
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
			if ($this->users[$nickname]['saluto'] == '') {
				$this->talk($irc, $data, 'Ciao ' . $nickname . ', ti saluto solo perché sono educato, ma ricorda che devi darmi il tuo saluto personale.');
			} else {
				$saluto = str_replace('$nick', $data->nick, $this->users[$nickname]['saluto']);
				$this->talk($irc, $data, $saluto);
			}
		} else {
			$this->talk($irc, $data, 'Ciao ' . $data->nick . ', non sei presente nel nostro database. Insultatelo, nao!');
		}
	}

	/**
	 * Quando un qualunque utente viene kickato dal canale, invia un messaggio nel canale ed all'utente vittima.
	 *
	 * @param	event
	 * @return	message
	 */
	function kick_response(&$irc, &$data)
	{
		$this->talk($irc, $data, '-1, avanti un altro! :P');
		$this->pvt_talk($irc, $data->messageex[0], 'Te la sei cercata! :P');
	}

	/**
	 * Nel caso il Bot fosse stato kickato, rientra nel canale.
	 *
	 * @param	event
	 * @return	message
	 */
	function kick_rejoin(&$irc, &$data)
	{
		if ($data->rawmessageex[3] == $this->config['nickname']) {
			$irc->join(array($this->server['channel']));
			$this->talk($irc, $data, 'Sono IMMORTALE ed esisto da quando il mondo era ancora una palla di fuoco, questo è il motivo della mia super intelligenza, io sono lo Divino Bot!');
		}
	}

	/**
	 * Effettua un kick generico. Questa funzione può essere richiamata per parole bannate o altro.
	 *
	 * @return	event
	 */
	function word_kick(&$irc, &$data)
	{
		$irc->kick($data->channel, $data->nick, 'Bla bla bla, EBBBASTA!!');
	}

	/**
	 * Verifica funzione esistente.
	 *
	 * @param	command
	 * @return	message
	 */
	function check_command(&$irc, &$data)
	{
		if (isset($data->messageex[0]) && $data->messageex[0][0] == '!' && !in_array(str_replace('!', '', $data->messageex[0]), get_class_methods($this)) && !$this->flood($data)) {
			$poggio = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ');
			$this->talk($irc, $data, 'Non conosco questo comando '.$data->nick.', quindi sarai calciorotato da Chuck Norris, smontato da McGyver e insultato da '.$poggio[array_rand($poggio,1)]);
		}
	}

	/**
	 * Insulto personalizzato su citazione.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function mention_insult(&$irc, &$data)
	{
		if (rand(0, 50) == 1 && $this->stop) {
			$this->talk($irc, $data, 'Sarete calciorotati il prima possibile se non mi date degli insulti personali!');
		}

		if ($this->stop && $data->messageex[0][0] != '!') {
			$messaggio = implode(' ', $data->messageex);

			foreach ($irc->_updateIrcUser($data) as $value) {
				$mention = strstr($messaggio, $value);

				$result = $this->is_user_exists($mention);

				$condition = $result['condition'];
				$nickname = $result['user'];

				if ($condition) {
					$insult_rand = $this->users[$nickname]['insulti'][array_rand($this->users[$nickname]['insulti'])];
					
					if ($value == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', ' . lcfirst($insult_rand));
					} elseif (isset($this->users[$nickname]['insulti'][0])) {
						$this->talk($irc, $data, $nickname . ', ' . lcfirst($insult_rand));
					}
				}
			}
		}
	}

	/**
	 * Informazioni generiche sul Bot.
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
	 * Link al Tumblr del canale.
	 *
	 * @param	string
	 * @return	string
	 */
	function tumblr(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->talk($irc, $data, 'Casa dolce casa... http://delirinotturni.tumblr.com/');
		}
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
	 * Descrizione e sinossi del comando richiesto.
	 *
	 * @param	command
	 * @return	message
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
	 * @return	message
	 */
	function online_users_list(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$nicklist = $this->remove_item_by_value($irc->_updateIrcUser($data), 'ChanServ');
			$this->talk($irc, $data, count($nicklist).' deliranti connessi: '.implode(', ', $nicklist));
		}
	}

	/**
	 * Utenti presenti nel database.
	 *
	 * @return	message
	 */
	function who(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$total_users = count(array_keys($this->users));
			$users_list = implode(', ', array_keys($this->users));
			$this->talk($irc, $data, $total_users . ' utenti nel database: ' . $users_list);
		}
	}

	/**
	 * Biografia dell'utente scelto o dell'utente stesso in mancanza di parametro.
	 *
	 * @param	nickname
	 * @return	message
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
	
	/**
	 * Account Twitter dell'utente scelto o dell'utente stesso in mancanza di parametro.
	 *
	 * @param	nickname
	 * @return	message
	 */
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
				if ($this->users[$nickname]['twitter'] == '') {
					$this->talk($irc, $data, $nickname . ' ancora non si decide a fornirmi il suo account Twitter.');
				} elseif ($this->users[$nickname]['twitter'] == 'NULL') {
					$this->talk($irc, $data, $nickname . ' ha preferito non rendere pubblico il suo account Twitter.');
				} else {
					$this->talk($irc, $data, 'Twitter di ' . $nickname . ' -- ' . $this->users[$nickname]['twitter']);
				}
			} else {
				$this->talk($irc, $data, 'L\'utente non inserito nel database. Tentativo di intrusione rilevato!');
			}
		}
	}

	/**
	 * Il benvenuto al nuovo inizializzato!
	 *
	 * @param	nickname
	 * @return	message
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
	 * Insulta l'utente richiesto. Inoltre può restituire il totale degli insulti presente nel database.
	 *
	 * @param	nickname
	 * @return	message
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
				} elseif (is_numeric($data->messageex[1]) && $n < $total_insults && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($this->insulti[$n]));
				} elseif (is_numeric($data->messageex[1]) && $n < $total_insults) {
					$this->talk($irc, $data, $this->insulti[$n]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($insult_rand));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo Divino Bot!');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Per info !man insulta');
			}
		}
	}

	/**
	 * Insulta l'utente richiesto per 5 volte consecutive.
	 *
	 * @param	nickname
	 * @return	message
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
					$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo Divino Bot!');
				} else {
				}
			}
		}
	}

	/**
	 * Insulta a morte l'utente richiesto. Inoltre può restituire il totale degli insulti di morte presente nel database.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function muori(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$total_deaths = count($this->deaths);
				$death_rand = $this->deaths[array_rand($this->deaths)];

				if (is_numeric($data->messageex[1]))
					$n = str_replace('-', '', (int)$data->messageex[1]);

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_deaths . ' morti presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n < $total_deaths && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($this->deaths[$n]));
				} elseif (is_numeric($data->messageex[1]) && $n < $total_deaths) {
					$this->talk($irc, $data, $this->deaths[$n]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($death_rand));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di uccidermi. Io sono lo Divino Bot!');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Per info !man muori');
			}
		}
	}

	/**
	 * Fa una supercazzola all'utente richiesto. Inoltre può restituire il totale delle supercazzole presente nel database.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function supercazzola(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$total_supercazzole = count($this->supercazzole);
				$supercazzola_rand = $this->supercazzole[array_rand($this->supercazzole)];

				if (is_numeric($data->messageex[1]))
					$n = str_replace('-', '', (int)$data->messageex[1]);

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_supercazzole . ' supercazzole presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n < $total_supercazzole && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($this->supercazzole[$n]));
				} elseif (is_numeric($data->messageex[1]) && $n < $total_supercazzole) {
					$this->talk($irc, $data, $this->supercazzole[$n]);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($supercazzola_rand));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di farmi una supercazzola. Io sono lo Divino Bot!');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Per info !man supercazzola');
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
			$nicklist = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ');
			$gaio = $nicklist[array_rand($nicklist, 1)];
			$this->talk($irc, $data, 'Il gay del momento è ' . $gaio);
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
			$nicklist = $this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), $data->nick);
			$love = $nicklist[array_rand($nicklist, 1)];
			$this->talk($irc, $data, $data->nick . ' Lovva ' . $love . ' <3');
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
		if (!$this->flood($data)) {
			$result = $this->is_user_exists($data->nick);

			$condition = $result['condition'];
			$nickname = $result['user'];

			$insult_rand = $this->insulti[array_rand($this->insulti)];

			if (isset($data->messageex[1]) && $data->messageex[1] == 'party') {
				$nicklist = $this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']);
				$alcohol = array('San Crispino', 'Tavernello', 'Olio Cuore', 'Estathé');
				$user_rand = $nicklist[array_rand($nicklist, 1)];
				$alcohol_rand = $alcohol[array_rand($alcohol)];
				
				$this->talk($irc, $data, 'Una bella damigiana di birra per tutti offerta da ' . $data->nick . '! Mentre per ' . $user_rand . ' solo ' . $alcohol_rand . '.');
			} elseif (isset($data->messageex[1])) {
				$this->talk($irc, $data, $data->messageex[1] . ', eccoti una bella birra fredda marchio "Delirio" offerta da ' . $data->nick . '!');
				
				$draw = $this->ascii_art('beer');
				for ($i=0; $i < count($draw); $i++) { 
					$this->talk($irc, $data, $draw[$i]);
				}
			} elseif (!isset($this->users[$nickname])) {
				$this->talk($irc, $data, 'Tu vorresti la nostra birra?? Non sei nel database brutta pustola.');
				$this->talk($irc, $data, $data->nick . ' ' . lcfirst($insult_rand));
			} elseif (isset($this->users[$nickname]['insulti']) && count($this->users[$nickname]['insulti']) < 3){
				$this->talk($irc, $data, 'A te niente birra brutto stronzetto, senza insulti personali non vai da nessuna parte,');
				$this->talk($irc, $data, $data->nick . ' ' . lcfirst($insult_rand));
			} else {
				$this->talk($irc, $data, $data->nick . ', eccoti una bella birra fredda marchio "Delirio" offerta dalla casa!');

				$draw = $this->ascii_art('beer');
				for ($i=0; $i < count($draw); $i++) { 
					$this->talk($irc, $data, $draw[$i]);
				}
			}
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
			$this->talk($irc, $data, 'Tirate fuori i vostri cucchiai, inizia il Nutella Party!');
		}
	}

	/**
	 * Sex.
	 *
	 * @param	string
	 * @return	string
	 */
	function sex(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$this->talk($irc, $data, $data->messageex[1] . ', eccoti una bella fungiazza di minchia offerta da quel cazzone di ' . $data->nick . '!');
				
				$draw = $this->ascii_art('penis');
				for ($i=0; $i < count($draw); $i++) { 
					$this->talk($irc, $data, $draw[$i]);
				}
			} else {
				$this->talk($irc, $data, $data->nick . ', eccoti una bella fungiazza di minchia offerta da me stesso medesimo!');

				$draw = $this->ascii_art('penis');
				for ($i=0; $i < count($draw); $i++) { 
					$this->talk($irc, $data, $draw[$i]);
				}
			}
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
						$termine = str_replace('+', '%20', $termine);
						$this->talk($irc, $data, 'http://www.fakku.net/search/' . $termine);
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
	 * Elenco di pastebin vari.
	 *
	 * @return	message
	 */
	function paste(&$irc, &$data)
	{
		$this->talk($irc, $data, 'http://pastebin.com/ | http://paste.kde.org/ | http://nopaste.voric.com/');
	}

	/**
	 * Permette di gestire il settaggio del Bot, come l'antiflood, il filtro e gli insulti su citazione.
	 * (Riservata agli operatori)
	 *
	 * @param	message
	 * @return	event
	 */
	function settings(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			switch ($data->messageex[1]) {
				case '-antiflood':
					if ($data->messageex[2] == 1) {
						$this->antiflood = TRUE;
						$this->talk($irc, $data, 'L\'antiflood è stato attivato.');
					} elseif ($data->messageex[2] == 0) {
						$this->antiflood = FALSE;
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
	 * @return	message
	 */
	function refresh(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$this->unset_vars();
			$this->set_vars();
			$this->talk($irc, $data, 'Database aggiornato.');
		} else {
			$this->talk($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}

	/**
	 * Cambia il nickname al Bot.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
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
	 * Join.
	 * (Riservata agli operatori)
	 *
	 * @param	channel
	 * @return	event
	 */
	function join(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$channel = $data->messageex[1];
				$irc->join($channel);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !join <canale>');
			}
		}
	}

	/**
	 * Esce dal canale scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	channel
	 * @return	event
	 */
	function part(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if(isset($data->messageex[1])) {
				$channel = $data->messageex[1];
				$irc->part($channel);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !part <canale>');
			}
		}
	}

	/**
	 * Esce e rientra dal canale scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	channel
	 * @return	event
	 */
	function rejoin(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$channel = $data->messageex[1];
				$irc->part($channel);
				$irc->join($channel);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !rejoin <canale>');
			}
		}
	}

	/**
	 * Assegna il grado d'operatore all'utente scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function op(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->op($channel, $nickname);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !op <nickname>');
			}
		}
	}

	/**
	 * Rimuove il grado d'operatore all'utente scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function deop(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->deop($channel, $nickname);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !deop <nickname>');
			}
		}
	}

	/**
	 * Assegna il voice all'utente scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function voice(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->voice($channel, $nickname);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !voice <nickname>');
			}
		}
	}

	/**
	 * Rimuove il voice all'utente scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function devoice(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->devoice($channel, $nickname);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !devoice <nickname>');
			}
		}
	}

	/**
	 * Effettua il kick sull'utente scelto.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @param	message
	 * @return	event
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !kick <nickname> <ragione>');
			}
		}
	}

	/**
	 * Effettua un kick random tra tutti gli utenti del canale ad esclusione del mandante del comando, il Bot ed il ChanServ.
	 * (Riservata agli operatori)
	 *
	 * @return	event
	 */
	function roulette(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$nicklist = $this->remove_item_by_value($this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ'), $data->nick);

			$n = rand(0, count($nicklist));

			$irc->kick($data->channel, $nicklist[$n], 'La roulette ha scelto te! SBANG!');
		}
	}

	/**
	 * Effettua il kick su tutti gli utenti del canale ad esclusione del mandante del comando, il Bot ed il ChanServ.
	 * (Riservata agli operatori)
	 *
	 * @return	event
	 */
	function clear(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$nicklist = $this->remove_item_by_value($this->remove_item_by_value($this->remove_item_by_value($irc->_updateIrcUser($data), $this->config['nickname']), 'ChanServ'), $data->nick);

			$this->talk($irc, $data, 'Il sistema è prossimo al colasso tra 5 secondi... COUNTDOWN STARTO!');
			
			for ($i=5; $i > 0; $i--) { 
				$this->talk($irc, $data, '-' . $i);
				$irc->setSendDelay(800);
			}

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
	 * @return	event
	 */
	function mute(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->mode($channel, '+q ' . $nickname, SMARTIRC_MEDIUM);
				$this->talk($irc, $data, $nickname . ', da ora in poi non puoi più parlare.');
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !mute <nickname>');
			}
		}
	}

	/**
	 * Rimuove il mode +q all'utente richiesto restituendo la possibilità di poter scrivere sul canale.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function unmute(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->mode($channel, '-q ' . $nickname, SMARTIRC_MEDIUM);
				$this->talk($irc, $data, $nickname . ', ora puoi tornare a parlare.');
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !unmute <nickname>');
			}
		}
	}

	/**
	 * Effettua il ban sull'utente selezionato.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function ban(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->ban($channel, $hostmask);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata: !ban <nickname>');
			}
		}
	}

	/**
	 * Rimuove il ban sull'utente selezionato.
	 * (Riservata agli operatori)
	 *
	 * @param	nickname
	 * @return	event
	 */
	function unban(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->unban($channel, $hostmask);
			} else {
				$this->priv_talk($irc, $data->nick, 'Sintassi comando errata: !unban <nickname>');
			}
		}
	}

	/**
	 * Battezza il nuovo utente.
	 * (Riservato agli operatori)
	 *
	 * @param	nickname
	 * @return	message
	 */
	function battezza(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			if (isset($data->messageex[1])) {
				$this->talk($irc, $data, $data->messageex[1] . ', ti battezzo nel nome del channel, delle puppe e dello spirito perverso. A te insulto iniziandoti a questa comunità delirante. Vai in pace ed espandi il nostro credo.');
				$this->talk($irc, $data, $data->messageex[1] . ', adesso sei uno di noi e puoi insultare con fierezza, per la birra gratis devi fornirci la tua biografia ed almeno un insulto personale. Ora puoi andartene a fanculo <3');
			}
		}
	}

	/**
	 * Disconnette il Bot.
	 * (Riservato agli operatori)
	 *
	 * @return	event
	 */
	function disconnect(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			$irc->quit('Addio mondo crudele!');
		} else {
			$this->talk($irc, $data, 'Chi ti credi di essere per darmi questi comandi?');
		}
	}
}