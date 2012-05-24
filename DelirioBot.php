<?php

define('VERSION', '0.2.2');

//Libreria di SmartIRC
include('SmartIRC.php');

class DelirioBot
{
	//Inizializzazione variabili di configurazione
	var $config = array();
	var $server = array();

	//Inizializzazione variabili di impostazione
	var $stop = FALSE;
	var $antiflood = TRUE;

	//Varie
	var $flooders = array();

	//Trivia
	var $answer;

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

		//Set variabili di connessione al database
		foreach ($config['database'] as $key => $value) {
			$this->config['database'][$key] = $value;
		}

		//Set variabili Bot
		foreach ($config['bot'] as $key => $value) {
			$this->config[$key] = $value;
		}

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
		$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '.*', $this, 'query_to_talk');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'mention_insult');
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
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!quote', $this, 'quote');
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
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!anime', $this, 'anime_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $this, 'packages_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!translate', $this, 'translate');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $this, 'paste');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $this, 'dado');
		//$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!trivia', $this, 'trivia');
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
	 * Connessione al database MySQL.
	 *
	 * @return	voide
	 */
	function db_connect()
	{
		$link = mysql_pconnect($this->config['database']['host'], $this->config['database']['user'], $this->config['database']['password']);
		if (!$link) {
			$this->talk($irc, $data, '[ERROR] Impossibile collegarsi al database.');
			//die();
		}

		$db_selected = mysql_select_db($this->config['database']['name'], $link);
		if (!$db_selected) {
			$this->talk($irc, $data, '[ERROR] Impossibile aprire il database selezionato.');
			//die();
		}
	}

	function query_to_talk(&$irc, &$data)
	{
		$irc->message(SMARTIRC_TYPE_CHANNEL, $this->server['channel'], $data->message);
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
	 * Restituisce tutte le righe di una query SQL in un array.
	 *
	 * @param	string
	 * @return	array
	 */
	function mysql_fetch_all($res)
	{
		while ($row = mysql_fetch_array($res)) {
			$return[] = $row;
		}

		return $return;
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
		$output = array();
		
		$this->db_connect();

		$query = "SELECT nickname FROM bot_users";
		$result = mysql_query($query);
		while($row = mysql_fetch_assoc($result)) {
			$users_list[] = $row['nickname'];
		}

		foreach ($users_list as $value) {
			if (preg_match("/$value/i", $nickname)) {
				$output['condition'] = TRUE;
				$output['user'] = $value;
				break;
			} else {
				$output['condition'] = FALSE;
			}
		}

		return $output;
	}

	/**
	 * Rimuove determinati elementi dall'array tramite il valore richiesto.
	 *
	 * @param	array
	 * @param	string
	 * @return	array
	 */
	function remove_users($users_list, $deleted)
	{
		// $list contiente la lista completa degli utenti
		// $users contiene i valori da rimuore

		foreach ($deleted as $val) {
			foreach ($users_list as $key => $value) {
				if ($value == $val)
					unset($users_list[$key]);
			}
		}

		return $users_list;
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

		$output = $this->is_user_exists($data->nick);

		$condition = $output['condition'];
		$nickname = $output['user'];

		if ($condition) {
			$this->db_connect();
				
			$query = "SELECT greeting FROM bot_users WHERE nickname = '{$nickname}'";
			$result = mysql_query($query);
			$user = mysql_fetch_assoc($result);

			if ($user['greeting'] == '') {
				$this->talk($irc, $data, 'Ciao ' . $data->nick . ', ti prendo a sassate se non mi dai il tuo saluto personale.');
			} else {
				$greeting = str_replace('$nick', $data->nick, $user['greeting']);
				$this->talk($irc, $data, $greeting);
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
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], $data->nick,'ChanServ'));
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

				$output = $this->is_user_exists($mention);

				$condition = $output['condition'];
				$nickname = $output['user'];

				if ($condition) {
					$this->db_connect();
				
					$query = "SELECT bot_personal_insults.insult FROM bot_users INNER JOIN bot_personal_insults ON bot_users.id = bot_personal_insults.user_id WHERE bot_users.nickname = '{$nickname}'";
					$result = mysql_query($query);
					$total_insults = mysql_num_rows($result);

					$query = "SELECT bot_personal_insults.insult FROM bot_users INNER JOIN bot_personal_insults ON bot_users.id = bot_personal_insults.user_id WHERE bot_users.nickname = '{$nickname}' ORDER BY RAND() LIMIT 0,1";
					$result = mysql_query($query);
					$rand_insult = mysql_result($result, 0);
					
					if ($value == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', ' . lcfirst($rand_insult));
					} elseif ($total_insults > 0) {
						$this->talk($irc, $data, $nickname . ', ' . lcfirst($rand_insult));
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
			$this->talk($irc, $data, 'I nostri log, meme, racconti ed altro ancora sul nostro Tumblr: http://delirinotturni.tumblr.com/');
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
			$this->talk($irc, $data, '[Comandi] !info, !tumblr, !man, !saluta, !ls, !who, !whoami, !noi, !insulta, !inalbera, !muori, !supercazzola, !calcio, !gaio, !amore, !birra, !nutella, !sex');
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
				$command = $data->messageex[1];

				$this->db_connect();
				
				$query = "SELECT description, synopsis FROM bot_manual WHERE command = '{$command}'";
				$result = mysql_query($query);
				$manual = mysql_fetch_assoc($result);

				$this->talk($irc, $data, '[Descrizione] ' . $command . ' -- ' . $manual['description']);
				$this->talk($irc, $data, '[Sinossi] ' . $manual['synopsis']);
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa: !man <comando>. Per ulteriori info: !man man.');
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
					$this->talk($irc, $data, 'Fottiti ' . $data->messageex[1]);
				} elseif ((in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname'])) {
					$this->talk($irc, $data, 'Fottiti ' . $data->nick);
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
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array('ChanServ'));

			$this->talk($irc, $data, count($users_list) . ' deliranti connessi: ' . implode(', ', $users_list));
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
			$this->db_connect();
				
			$query = "SELECT nickname FROM bot_users";
			$result = mysql_query($query);
			while($row = mysql_fetch_assoc($result)) {
				$users[] = $row['nickname'];
			}

			$total_users = mysql_num_rows($result);
			$users_list = implode(', ', $users);

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
				$output = $this->is_user_exists($data->messageex[1]);
			} else {
				$output = $this->is_user_exists($data->nick);
			}

			$condition = $output['condition'];
			$nickname = $output['user'];

			if ($condition) {
				$this->db_connect();
				$query = "SELECT bio FROM bot_users WHERE nickname = '{$nickname}'";
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);

				$this->talk($irc, $data, 'Biografia di ' . $nickname . ' -- ' . $row['bio']);
			} else {
				$this->talk($irc, $data, 'L\'utente non è inserito nel database. Tentativo di intrusione rilevato!');
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
				$output = $this->is_user_exists($data->messageex[1]);
			} else {
				$output = $this->is_user_exists($data->nick);
			}

			$condition = $output['condition'];
			$nickname = $output['user'];

			if ($condition) {
				$this->db_connect();
				$query = "SELECT twitter FROM bot_users WHERE nickname = '{$nickname}'";
				$result = mysql_query($query);
				$row = mysql_fetch_assoc($result);

				if ($row['twitter'] == '') {
					$this->talk($irc, $data, $nickname . ' ancora non si decide a fornirmi il suo account Twitter.');
				} elseif ($row['twitter'] == 'NULL') {
					$this->talk($irc, $data, $nickname . ' ha preferito non rendere pubblico il suo account Twitter.');
				} else {
					$this->talk($irc, $data, 'Twitter di ' . $nickname . ' -- https://twitter.com/' . $row['twitter']);
				}
			} else {
				$this->talk($irc, $data, 'L\'utente non è inserito nel database. Tentativo di intrusione rilevato!');
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
			if (isset($data->messageex[1]) && in_array($data->messageex[1], $irc->_updateIrcUser($data))) {
				$this->talk($irc, $data, $data->messageex[1] . ' è ora uno di noi, uno di noi, uno di noi, uno di noi, un delirante come noi!');
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa: !noi <nickname>. Per ulteriori info: !man noi.');
			}
		}
	}

	/**
	 * Salva, elimina, scrive, le citazioni inserite dagli utenti.
	 *
	 * @param	message
	 * @return	message
	 */
	function quote(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->db_connect();

			if (isset($data->messageex[1])) {
				if ($data->messageex[1] == 'add') {
					$author = $data->nick;
					$added = time();

					$quote = trim(str_replace('!quote add', '', implode(' ', $data->messageex)));

					$query = "INSERT INTO bot_quotes (author, added, quote) VALUES ('{$author}', '{$added}', '{$quote}')";
					$result = mysql_query($query);

					if ($result) {
						$id = mysql_insert_id();
						$this->talk($irc, $data, 'Aggiunta citazione #' . $id . ' di ' . $author);
					} else {
						$this->talk($irc, $data, 'Errore.');
					}
				} elseif ($data->messageex[1] == 'read' && is_numeric($data->messageex[2])) {
					$num_quote = $data->messageex[2];

					$query = "SELECT * FROM bot_quotes WHERE id = '{$num_quote}'";
					$result = mysql_query($query);

					if (mysql_num_rows($result) > 0) {
						$quote = mysql_fetch_assoc($result);
						$this->talk($irc, $data, $quote['quote'] . ' [Cit. #' . $quote['id'] . ' aggiunta da ' . $quote['author'] . ', il ' . date('d F Y - H:i', $quote['added']) . ']');
					} else {
						$this->talk($irc, $data, 'Citazione inesistente.');
					}
				} elseif ($data->messageex[1] == 'del' && in_array($data->nick, $irc->_GetIrcOp($data))) {
					$num_quote = $data->messageex[2];

					$query = "DELETE FROM bot_quotes WHERE id = '{$num_quote}'";
					$result = mysql_query($query);

					if ($result) {
						$this->talk($irc, $data, $quote['quote'] . 'Citazione #' . $num_quote . ' eliminata!');
					} else {
						$this->talk($irc, $data, 'Citazione inesistente.');
					}
				} elseif ($data->messageex[1] == 'mine') {
					$author = $data->nick;

					$query = "SELECT id FROM bot_quotes WHERE author = '{$author}'";
					$result = mysql_query($query);

					if (mysql_num_rows($result) > 0) {
						while($row = mysql_fetch_assoc($result)) {
							$quotes_id[] = $row['id'];
						}

						$total_quotes = mysql_num_rows($result);
						$quotes_id_list = implode(', ', $quotes_id);
						
						$this->talk($irc, $data, $author . ' hai aggiunto ' . $total_quotes . ' citazioni: ' . $quotes_id_list . '.');
					} else {
						$this->talk($irc, $data, 'Non hai aggiunto citazioni.');
					}
				}
			} else {
				$query = "SELECT * FROM bot_quotes ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$quote = mysql_fetch_assoc($result);

				$this->talk($irc, $data, $quote['quote'] . ' [Cit. #' . $quote['id'] . ' aggiunta da ' . $quote['author'] . ', il ' . date('d F Y - H:i', $quote['added']) . ']');
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
				$this->db_connect();
				
				$query = "SELECT insult FROM bot_insults";
				$result = mysql_query($query);
				$total_insults = mysql_num_rows($result);

				$query = "SELECT insult FROM bot_insults ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$rand_insult = mysql_result($result, 0);

				if (is_numeric($data->messageex[1])) {
					$n = abs((int)$data->messageex[1]);

					$query = "SELECT insult FROM bot_insults WHERE id = '{$n}'";
					$result = mysql_query($query);
					$insult = mysql_result($result, 0);
				}

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_insults . ' insulti presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_insults && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($insult));
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_insults) {
					$this->talk($irc, $data, $insult);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($rand_insult));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo Divino Bot!');
					} else {
						$this->talk($irc, $data, $data->nick . ', chi sarebbe ' . $data->messageex[1] . '? E\' tua suocera o la tua mano destra?');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa: !insulta <nickname>. Per ulteriori info: !man insulta.');
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
					$this->db_connect();

					for ($i=0; $i < 5; $i++) {			
						$query = "SELECT insult FROM bot_insults ORDER BY RAND() LIMIT 0,1";
						$result = mysql_query($query);
						$rand_insult = mysql_result($result, 0);

						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($rand_insult));
					}
				} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
					$this->talk($irc, $data, $data->nick . ', non puoi permetterti di insultarmi. Io sono lo Divino Bot!');
				} else {
					$this->talk($irc, $data, $data->nick . ', chi sarebbe ' . $data->messageex[1] . '? E\' tua suocera o la tua mano destra?');
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa: !inalbera <nickname>. Per ulteriori info: !man inalbera.');
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
				$this->db_connect();
				
				$query = "SELECT death FROM bot_deaths";
				$result = mysql_query($query);
				$total_deaths = mysql_num_rows($result);

				$query = "SELECT death FROM bot_deaths ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$rand_death = mysql_result($result, 0);

				if (is_numeric($data->messageex[1])) {
					$n = abs((int)$data->messageex[1]);

					$query = "SELECT death FROM bot_deaths WHERE id = '{$n}'";
					$result = mysql_query($query);
					$death = mysql_result($result, 0);
				}

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_deaths . ' tipi di morte presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_deaths && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($death));
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_deaths) {
					$this->talk($irc, $data, $death);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($rand_death));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di uccidermi. Io sono lo Divino Bot!');
					} else {
						$this->talk($irc, $data, $data->nick . ', chi sarebbe ' . $data->messageex[1] . '? E\' tua suocera o la tua mano destra?');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa: !muori <nickname>. Per ulteriori info: !man muori.');
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
				$this->db_connect();
				
				$query = "SELECT supercazzola FROM bot_supercazzole";
				$result = mysql_query($query);
				$total_supercazzole = mysql_num_rows($result);

				$query = "SELECT supercazzola FROM bot_supercazzole ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$rand_supercazzola = mysql_result($result, 0);

				if (is_numeric($data->messageex[1])) {
					$n = abs((int)$data->messageex[1]);

					$query = "SELECT supercazzola FROM bot_supercazzole WHERE id = '{$n}'";
					$result = mysql_query($query);
					$supercazzola = mysql_result($result, 0);
				}

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_supercazzole . ' supercazzole presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_supercazzole && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($supercazzola));
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_supercazzole) {
					$this->talk($irc, $data, $supercazzola);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($rand_supercazzola));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non puoi permetterti di farmi una supercazzola. Io sono lo Divino Bot!');
					} else {
						$this->talk($irc, $data, $data->nick . ', chi sarebbe ' . $data->messageex[1] . '? E\' tua suocera o la tua mano destra?');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa: !supercazzola <nickname>. Per ulteriori info: !man supercazzola.');
			}
		}
	}

	/**
	 * L'utente riceve N calcio random.
	 *
	 * @return	message
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
	 * Decreta il gaio del momento.
	 *
	 * @return	message
	 */
	function gaio(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], 'ChanServ'));

			$gaio = $users_list[array_rand($users_list, 1)];

			$this->talk($irc, $data, 'Il gay del momento è ' . $gaio);
		}
	}

	/**
	 * Chi ama chi!
	 *
	 * @return	message
	 */
	function amore(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], $data->nick, 'ChanServ'));
			
			$love = $users_list[array_rand($users_list, 1)];
			
			$this->talk($irc, $data, $data->nick . ' lovva ' . $love . ' <3');
		}
	}

	/**
	 * Offre la birra a tutto il chan o un singolo utente o a se stessi.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function birra(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$output = $this->is_user_exists($data->nick);

			$condition = $output['condition'];
			$nickname = $output['user'];

			$this->db_connect();

			$query = "SELECT bot_personal_insults.insult FROM bot_users INNER JOIN bot_personal_insults ON bot_users.id = bot_personal_insults.user_id WHERE bot_users.nickname = '{$nickname}'";
			$result = mysql_query($query);
			$total_insults = mysql_num_rows($result);

			$query = "SELECT insult FROM bot_insults ORDER BY RAND() LIMIT 0,1";
			$result = mysql_query($query);
			$rand_insult = mysql_result($result, 0);

			if (isset($data->messageex[1]) && $data->messageex[1] == 'party') {
				$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], $data->nick, 'ChanServ'));

				$alcohol = array('San Crispino', 'Tavernello', 'Olio Cuore', 'Estathé');
				
				$user_rand = $users_list[array_rand($users_list, 1)];
				$alcohol_rand = $alcohol[array_rand($alcohol)];
				
				$this->talk($irc, $data, 'Una bella damigiana di birra per tutti offerta da ' . $data->nick . '! Mentre per ' . $user_rand . ' solo ' . $alcohol_rand . '.');
			} elseif (isset($data->messageex[1])) {
				$this->talk($irc, $data, $data->messageex[1] . ', eccoti una bella birra fredda marchio "Delirio" offerta da ' . $data->nick . '!');
				
				$draw = $this->ascii_art('beer');
				for ($i=0; $i < count($draw); $i++) { 
					$this->talk($irc, $data, $draw[$i]);
				}
			} elseif (!$condition) {
				$this->talk($irc, $data, 'Tu vorresti la nostra birra?? Non sei nel database brutta pustola.');
				$this->talk($irc, $data, $data->nick . ' ' . lcfirst($rand_insult));
			} elseif ($total_insults > 0 && $total_insults < 3){
				$this->talk($irc, $data, 'A te niente birra brutto stronzetto, senza insulti personali non vai da nessuna parte.');
				$this->talk($irc, $data, $data->nick . ' ' . lcfirst($rand_insult));
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
	 * Attiva il Nutella Party.
	 *
	 * @return	message
	 */
	function nutella(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->talk($irc, $data, 'Tirate fuori i vostri cucchiai, inizia il Nutella Party!');
		}
	}

	/**
	 * XXX!
	 *
	 * @param	nickname
	 * @return	message
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
	 * Ricerca avanzata su Google.
	 *
	 * @param	string
	 * @return	string
	 */
	function google_search(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$adds_vars = '';
			
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

			if (isset($data->messageex[1])) {
				$this->talk($irc, $data,'https://www.google.it/search?q='.$termine.$adds_vars);
			} elseif (!isset($data->messageex[1])) {
				$this->talk($irc, $data, '!google [-img | -vid | -l:it] <query>');
			} else {
				$this->talk($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * Ricerca su YouTube.
	 *
	 * @param	string
	 * @return	string
	 */
	function youtube_search(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$termine = preg_replace('/[^a-zA-Z0-9\s]/', '', urldecode(str_replace('!yt ', '', implode(' ', $data->messageex))));
			$termine = str_replace(' ', '+', $termine);

			if (isset($data->messageex[1])) {
				$this->talk($irc, $data, 'http://www.youtube.com/results?search_query='.$termine);
			} elseif (!isset($data->messageex[1])) {
					$this->talk($irc, $data, '!yt <query>');
			} else {
				$this->talk($irc, $data, 'Non rompere le palle '.$data->nick.' ho di meglio da fare io...');
			}
		}
	}

	/**
	 * Ricerca su siti pornografici.
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
	 * Traduzione di una stringa su Google Translate.
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
		if (!$this->flood($data)) {
			$this->talk($irc, $data, 'http://pastebin.com/ | http://paste.kde.org/ | http://nopaste.voric.com/');
		}
	}


	/**
	 * Lancia N dadi da M facce.
	 *
	 * @param	int
	 * @return	message
	 */
	function dado(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$dadi = $data->messageex[1];
			$facce = $data->messageex[2];

			if (is_numeric($dadi) && is_numeric($facce)) {
				$dadi = intval(abs($dadi));
				$facce = intval(abs($facce));

				if ($dadi < 1)
					$dadi = 1;

				if ($dadi > 10)
					$dadi = 10;

				if ($facce < 6)
					$facce = 6;

				if ($facce > 100)
					$facce = 100;

				for ($i=0; $i < $dadi; $i++) { 
					$results[] = rand(1, $facce);
				}

				$display_results = implode(', ', $results);

				if ($dadi == 1) {
					$this->talk($irc, $data, $data->nick . ', hai lanciato ' . $dadi . ' dado da ' . $facce . ' facce e hai ottenuto: ' . $display_results);
				} else {
					$this->talk($irc, $data, $data->nick . ', hai lanciato ' . $dadi . ' dadi da ' . $facce . ' facce e hai ottenuto: ' . $display_results);
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Per info !man dado');
			}
		}
	}

	/**
	 * Gioco a domande.
	 *
	 * @param	message
	 * @return	message
	 */
	function trivia(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$this->db_connect();

			if (isset($data->messageex[1])) {
				if (is_numeric($data->messageex[1])) {
					$num_questions = $data->messageex[1];
					
					$query = "SELECT question, answer FROM bot_trivia ORDER BY RAND() LIMIT 0,1";
					$result = mysql_query($query);
					$trivia = mysql_fetch_assoc($result);

					$n = 1;

					$this->talk($irc, $data, 'Domanda #' . $n . ': ' . $trivia['question']);
					
					$this->answer = $trivia['answer'];
					
					$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, "/$this->answer/i", $this, 'trivia_matching');
				}
			} else {
				$this->talk($irc, $data, 'Errore.');
			}
		}
	}

	/**
	 * Trova la risposta giusta nel Trivia.
	 *
	 * @param	event
	 * @return	message
	 */
	function trivia_matching(&$irc, &$data)
	{
		$user = $data->nick;

		$this->talk($irc, $data, 'Vince ' . $user . '; Risposta: ' . $this->answer . '; Tempo impiegato: ' . $time . 's; Punti: ' . $score);

		$this->db_connect();

		$query = "SELECT score FROM bot_trivia_highscore WHERE nickname = '{$user}'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);

		if (mysql_num_rows($result) > 0) {
			$sum_points = $row[0]+1;
			$query = "UPDATE bot_trivia_highscore SET score = '{$sum_points}' WHERE nickname = '{$user}'";
			$result = mysql_query($query);
		} else {
			$query = "INSERT INTO bot_trivia_highscore (nickname, score) VALUES ('{$user}', '1')";
			$result = mysql_query($query);
		}
	}

	/**
	 * Permette di gestire il settaggio del Bot, come l'antiflood e gli insulti su citazione.
	 * (Riservata agli operatori)
	 *
	 * @param	message
	 * @return	event
	 */
	function settings(&$irc, &$data)
	{
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
			switch ($data->messageex[1]) {
				case 'antiflood':
					if ($data->messageex[2] == 1) {
						$this->antiflood = TRUE;
						$this->talk($irc, $data, 'L\'antiflood è stato attivato.');
					} elseif ($data->messageex[2] == 0) {
						$this->antiflood = FALSE;
						$this->talk($irc, $data, 'L\'antiflood è stato disattivato.');
					}
					break;
				
				case 'insulta':
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
				$this->config['nickname'] = $newnick;
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
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], $data->nick,'ChanServ'));

			$n = rand(0, count($users_list));

			$irc->kick($data->channel, $users_list[$n], 'La roulette ha scelto te! SBANG!');
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
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], $data->nick,'ChanServ'));

			$this->talk($irc, $data, 'Il sistema è prossimo al colasso tra 5 secondi... COUNTDOWN STARTO!');
			
			for ($i=5; $i > 0; $i--) { 
				$this->talk($irc, $data, '-' . $i);
				$irc->setSendDelay(800);
			}

			for ($i=0; $i < count($users_list); $i++) { 
				$irc->kick($data->channel, $users_list[$i], 'Kernel Panic!');
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
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
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
		if (in_array($data->nick, $irc->_GetIrcOp($data))) {
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