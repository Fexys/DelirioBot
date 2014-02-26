<?php

define('VERSION', '0.3');

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

	//Trivia
	var $trivia = array();

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
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $this, 'proverb_on_mention');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!info$', $this, 'info');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!help$', $this, 'help');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!man', $this, 'man');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!saluta', $this, 'give_greeting');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ls$', $this, 'online_users_list');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!who$', $this, 'who');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!whoami', $this, 'whoami');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!twitter', $this, 'twitter');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!quote', $this, 'quote');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!proverbio', $this, 'proverb');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!battuta', $this, 'battuta');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!lucky$', $this, 'lucky');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!birra', $this, 'birra');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!google', $this, 'google_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!youtube', $this, 'youtube_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!anime', $this, 'anime_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!pkg', $this, 'packages_search');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!translate', $this, 'translate');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!paste', $this, 'paste');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!dado', $this, 'dado');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!roulette$', $this, 'roulette');
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
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!random$', $this, 'userslist_roulette');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!clear$', $this, 'clear');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!mute', $this, 'mute');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!unmute', $this, 'unmute');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!ban', $this, 'ban');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!unban', $this, 'unban');
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

	/**
	 * Il Bot scrive in channel i messaggi ricevuti in privato.
	 *
	 * @param	string
	 * @return	string
	 */
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
					'      ]~,"-.-~~[       Cheers!',
					'    .=])\' (;  ([',
					'    | ]:: \'    [',
					'    \'=]): .)  ([',
					'      |:: \'    |',
					'       ~~----~~'
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
				$this->talk($irc, $data, 'Hey! Ciao ' . $data->nick . ', ma non hai ancora un saluto personale?');
			} else {
				$greeting = str_replace('$nick', $data->nick, $user['greeting']);
				$this->talk($irc, $data, $greeting);
			}
		} else {
			$this->talk($irc, $data, 'Ciao ' . $data->nick . '!');
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
		}
	}

	/**
	 * Effettua un kick generico. Questa funzione può essere richiamata per parole bannate o altro.
	 *
	 * @return	event
	 */
	function word_kick(&$irc, &$data)
	{
		$irc->kick($data->channel, $data->nick, 'Parola bandita!');
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
			$this->talk($irc, $data, 'Non conosco questo comando ' . $data->nick);
		}
	}

	/**
	 * Proverbio personalizzato su citazione.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function proverb_on_mention(&$irc, &$data)
	{
		if ($this->stop && $data->messageex[0][0] != '!') {
			$messaggio = implode(' ', $data->messageex);

			foreach ($irc->_updateIrcUser($data) as $value) {
				$mention = strstr($messaggio, $value);

				$output = $this->is_user_exists($mention);

				$condition = $output['condition'];
				$nickname = $output['user'];

				if ($condition) {
					$this->db_connect();
				
					$query = "SELECT bot_personal_proverbs.proverb FROM bot_users INNER JOIN bot_personal_proverbs ON bot_users.id = bot_personal_proverbs.user_id WHERE bot_users.nickname = '{$nickname}'";
					$result = mysql_query($query);
					$total_proverbs = mysql_num_rows($result);

					$query = "SELECT bot_personal_proverbs.proverb FROM bot_users INNER JOIN bot_personal_proverbs ON bot_users.id = bot_personal_proverbs.user_id WHERE bot_users.nickname = '{$nickname}' ORDER BY RAND() LIMIT 0,1";
					$result = mysql_query($query);
					$rand_proverb = mysql_result($result, 0);
					
					if ($value == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', ' . lcfirst($rand_proverb));
					} elseif ($total_proverbs > 0) {
						$this->talk($irc, $data, $nickname . ', ' . lcfirst($rand_proverb));
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
			$this->talk($irc, $data, $this->config['nickname'] . ' [ver ' . VERSION . ']. Sorgenti, idee e segnalazioni bug su https://github.com/Fexys/Delirante/');
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
			$this->talk($irc, $data, '[Comandi] !info, !man, !saluta, !ls, !who, !whoami, !twitter, !quote, !proverbio, !battuta, !lucky, !birra');
			$this->talk($irc, $data, '[Strumenti] !google, !yt, !anime, !pkg, !translate, !paste');
			$this->talk($irc, $data, '[Giochi] !dado, !roulette');
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

				if (mysql_num_rows($result) > 0) {
					$this->talk($irc, $data, '[Descrizione] ' . $command . ' -- ' . $manual['description']);
					$this->talk($irc, $data, '[Sinossi] ' . $manual['synopsis']);
				} else {
					$this->talk($irc, $data, 'Comando inesistente.');
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!man <comando>`. Per ulteriori informazioni `!man man`.');
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
					$this->talk($irc, $data, 'Salute a te, o ' . $data->messageex[1]);
				} elseif ((in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname'])) {
					$this->talk($irc, $data, 'Salute a te, o ' . $data->nick);
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
				$this->talk($irc, $data, 'L\'utente non è inserito nel database.');
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
					$this->talk($irc, $data, $nickname . ' non ha ancora fornito il suo account Twitter.');
				} elseif ($row['twitter'] == 'NULL') {
					$this->talk($irc, $data, $nickname . ' ha preferito non rendere pubblico il suo account Twitter.');
				} else {
					$this->talk($irc, $data, 'Twitter di ' . $nickname . ' -- https://twitter.com/' . $row['twitter']);
				}
			} else {
				$this->talk($irc, $data, 'L\'utente non è inserito nel database.');
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
						$this->talk($irc, $data, $quote['quote'] . ' [Cit. #' . $quote['id'] . ' di ' . $quote['author'] . ', ' . date('d/n/Y H:i', $quote['added']) . ']');
					} else {
						$this->talk($irc, $data, 'Citazione inesistente.');
					}
				} elseif ($data->messageex[1] == 'read' && !is_numeric($data->messageex[2])) {
					$author = $data->messageex[2];

					$query = "SELECT id FROM bot_quotes WHERE author = '{$author}'";
					$result = mysql_query($query);

					if (mysql_num_rows($result) > 0) {
						while($row = mysql_fetch_assoc($result)) {
							$quotes_id[] = $row['id'];
						}

						$total_quotes = mysql_num_rows($result);
						$quotes_id_list = implode(', ', $quotes_id);
						
						$this->talk($irc, $data, $author . ' ha aggiunto ' . $total_quotes . ' citazioni: ' . $quotes_id_list . '.');
					} else {
						$this->talk($irc, $data, 'Non ha aggiunto citazioni.');
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
				} else {
					$this->talk($irc, $data, 'Sintassi comando errata. Usa `!quote` per leggere una citazione, oppure, `!quote add <quote>` per aggiurne una nuova. Per ulteriori informazioni `!man quote`.');
				}
			} else {
				$query = "SELECT * FROM bot_quotes ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$quote = mysql_fetch_assoc($result);

				$this->talk($irc, $data, $quote['quote'] . ' [Cit. #' . $quote['id'] . ' di ' . $quote['author'] . ', ' . date('d/n/Y H:i', $quote['added']) . ']');
			}
		}
	}

	/**
	 * Invia un proverbio all'utente richiesto. Inoltre può restituire il totale dei proverbi presenti nel database.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function proverb(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$this->db_connect();
				
				$query = "SELECT proverb FROM bot_proverbs";
				$result = mysql_query($query);
				$total_proverbs = mysql_num_rows($result);

				$query = "SELECT proverb FROM bot_proverbs ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$rand_proverb = mysql_result($result, 0);

				if (is_numeric($data->messageex[1])) {
					$n = abs((int)$data->messageex[1]);

					$query = "SELECT proverb FROM bot_proverbs WHERE id = '{$n}'";
					$result = mysql_query($query);
					$proverb = mysql_result($result, 0);
				}

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_proverbs . ' proverbi presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_proverbs && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($proverb));
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_proverbs) {
					$this->talk($irc, $data, $proverb);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($rand_proverb));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', non ha senso inviare un proverbio a me, io sono un bot!');
					} else {
						$this->talk($irc, $data, $data->nick . ', chi sarebbe ' . $data->messageex[1] . '?');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!proverbio <nickname>`. Per ulteriori informazioni `!man proverbio`.');
			}
		}
	}

	/**
	 * Fa una battuta all'utente richiesto. Inoltre può restituire il totale delle battuta presenti nel database.
	 *
	 * @param	nickname
	 * @return	message
	 */
	function battuta(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			if (isset($data->messageex[1])) {
				$this->db_connect();
				
				$query = "SELECT battuta FROM bot_battute";
				$result = mysql_query($query);
				$total_battute = mysql_num_rows($result);

				$query = "SELECT battuta FROM bot_battute ORDER BY RAND() LIMIT 0,1";
				$result = mysql_query($query);
				$rand_battuta = mysql_result($result, 0);

				if (is_numeric($data->messageex[1])) {
					$n = abs((int)$data->messageex[1]);

					$query = "SELECT battuta FROM bot_battute WHERE id = '{$n}'";
					$result = mysql_query($query);
					$battuta = mysql_result($result, 0);
				}

				if ($data->messageex[1] == '-c') {
					$this->talk($irc, $data, $total_battute . ' battute presenti nel database.');
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_battute && isset($data->messageex[2]) && in_array($data->messageex[2], $irc->_updateIrcUser($data))) {
					$this->talk($irc, $data, $data->messageex[2] . ', ' . lcfirst($battuta));
				} elseif (is_numeric($data->messageex[1]) && $n <= $total_battute) {
					$this->talk($irc, $data, $battuta);
				} else {
					if (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] != $this->config['nickname']) {
						$this->talk($irc, $data, $data->messageex[1] . ', ' . lcfirst($rand_battuta));
					} elseif (in_array($data->messageex[1], $irc->_updateIrcUser($data)) && $data->messageex[1] == $this->config['nickname']) {
						$this->talk($irc, $data, $data->nick . ', come ti permetti di fare una battuta a me?');
					} else {
						$this->talk($irc, $data, $data->nick . ', chi sarebbe ' . $data->messageex[1] . '?');
					}
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!battuta <nickname>`. Per ulteriori informazioni `!man battuta`.');
			}
		}
	}

	/**
	 * Decreta il fortunato del giorno.
	 *
	 * @return	message
	 */
	function lucky(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], 'ChanServ'));

			$lucky = $users_list[array_rand($users_list, 1)];

			$this->talk($irc, $data, 'Il fortunato di oggi è ' . $lucky);
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

			$query = "SELECT bot_personal_proverbs.proverb FROM bot_users INNER JOIN bot_personal_proverbs ON bot_users.id = bot_personal_proverbs.user_id WHERE bot_users.nickname = '{$nickname}'";
			$result = mysql_query($query);
			$total_proverbs = mysql_num_rows($result);

			if (isset($data->messageex[1]) && $data->messageex[1] == 'party') {
				$users_list = $this->remove_users($irc->_updateIrcUser($data), array($this->config['nickname'], $data->nick, 'ChanServ'));

				$alcohol = array('San Crispino', 'Tavernello', 'Olio Cuore', 'Estathé');
				
				$user_rand = $users_list[array_rand($users_list, 1)];
				$alcohol_rand = $alcohol[array_rand($alcohol)];
				
				$this->talk($irc, $data, 'Una bella damigiana di birra per tutti offerta da ' . $data->nick . '! Mentre per ' . $user_rand . ' solo ' . $alcohol_rand . '.');
			} elseif (isset($data->messageex[1])) {
				$this->talk($irc, $data, $data->messageex[1] . ', eccoti una bella birra fredda offerta da ' . $data->nick . '!');
				
				$draw = $this->ascii_art('beer');
				for ($i=0; $i < count($draw); $i++) { 
					$this->talk($irc, $data, $draw[$i]);
				}
			} elseif (!$condition) {
				$this->talk($irc, $data, 'Tu vorresti la nostra birra? Ma non sei nel database!');
			} elseif ($total_proverbs > 0 && $total_proverbs < 3){
				$this->talk($irc, $data, 'A te niente birra, senza un saluto personale non vai da nessuna parte.');
			} else {
				$this->talk($irc, $data, $data->nick . ', eccoti una bella birra fredda offerta dalla casa!');

				$draw = $this->ascii_art('beer');
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
			
			$query = urldecode($query);
			$query = str_replace('!google ', '', implode(' ', $data->messageex));

			$str = preg_match("/\-l:([^ ]*)/", $query, $lang);
			if (isset($lang[1])) {
				$adds_vars .= '&hl=' . $lang[1];
				$query = trim(preg_replace("/\-l([^ ]*)/", '', $query));
			}

			$str = preg_match("/\-([^ ]*) (.*)/", $query, $output);
			if (isset($output[1])) {
				if ($output[1] == 'img')
					$adds_vars .= '&tbm=isch';

				if ($output[1] == 'vid')
					$adds_vars .= '&tbm=vid';

				$query = str_replace(' ', '+', trim($output[2]));
			} else {
				$query = str_replace(' ', '+', $query);
			}

			if (isset($data->messageex[1])) {
				$this->talk($irc, $data,'https://www.google.it/search?q=' . $query . $adds_vars);
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!google <query>` per una semplice ricerca. Per una ricerca avanzata usa `!google [-img | -vid | -l:lang] <query>` scegliendo una o più tra le opzioni proposte. Per ulteriori informazioni `!man google`.');
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
			$query = urldecode($query);
			$query = str_replace('!youtube ', '', implode(' ', $data->messageex));
			$query = str_replace(' ', '+', $query);

			if (isset($data->messageex[1])) {
				$this->talk($irc, $data, 'https://www.youtube.com/results?search_query=' . $query);
			} else {
				$this->talk($irc, $data, 'Devi inserire una o più parole chiave da ricercare, idiota!');
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
			$query = urldecode($query);
			$query = str_replace('!anime ', '', implode(' ', $data->messageex));
			$query = preg_replace("/\-([^ ]*)/", '', $query);
			$query = trim($query);
			$query = str_replace(' ', '+', $query);

			if ($query == '') {
				$this->talk($irc, $data, 'Devi inserire una o più parole chiave da ricercare, idiota!');
			} elseif (isset($data->messageex[1])) {
				switch ($data->messageex[1]) {
					case '-mal:anime':
						$this->talk($irc, $data, 'http://myanimelist.net/anime.php?q=' . $query);
						break;

					case '-mal:manga':
						$this->talk($irc, $data, 'http://myanimelist.net/manga.php?q=' . $query);
						break;

					case '-animeclick':
						$this->talk($irc, $data, 'http://www.animeclick.it/anime.php?titolo=' . $query);
						break;

					case '-fansub':
						$this->talk($irc, $data, 'http://www.animeclick.it/ListaFansubs.php?keywords=' . $query . '&ftip=T');
						break;

					default:
						$this->talk($irc, $data, 'Sintassi comando errata. Usa `!anime [-mal:anime | -mal:manga | -animeclick | -fansub] <query>` scegliendo una tra le opzioni proposte. Per ulteriori informazioni `!man anime`.');
						break;
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!anime [-mal:anime | -mal:manga | -animeclick | -fansub] <query>` scegliendo una tra le opzioni proposte. Per ulteriori informazioni `!man anime`.');
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
			$query = urldecode($query);
			$query = str_replace('!pkg ', '', implode(' ', $data->messageex));
			$query = preg_replace("/\-([^ ]*)/", '', $query);
			$query = trim($query);
			$query = str_replace(' ', '+', $query);

			if ($query == '') {
				$this->talk($irc, $data, 'Devi inserire una o più parole chiave da ricercare, idiota!');
			} elseif (isset($data->messageex[1])) {
				switch ($data->messageex[1]) {
					case '-rpm':
						$this->talk($irc, $data, 'http://rpmfusion.org/RPM%20Fusion?action=fullsearch&value=' . $query . '&titlesearch=Titoli');
						break;

					case '-deb':
						$this->talk($irc, $data, 'http://packages.debian.org/search?keywords=' . $query);
						break;

					case '-ubu':
						$this->talk($irc, $data, 'http://packages.ubuntu.com/search?keywords=' . $query);
						break;

					case '-arch':
						$this->talk($irc, $data, 'http://www.archlinux.org/packages/?q=' . $query);
						break;

					case '-aur':
						$this->talk($irc, $data, 'https://aur.archlinux.org/packages.php?K=' . $query);
						break;

					case '-suse':
						$this->talk($irc, $data, 'http://software.opensuse.org/search?q=' . $query);
						break;

					default:
						$this->talk($irc, $data, 'Sintassi comando errata. Usa `!pkg [-rpm | -deb | -ubu | -arch | -aur | -suse] <query>` scegliendo una tra le opzioni proposte. Per ulteriori informazioni `!man pkg`.');
						break;
				}
			} else {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!pkg [-rpm | -deb | -ubu | -arch | -aur | -suse] <query>` scegliendo una tra le opzioni proposte. Per ulteriori informazioni `!man pkg`.');
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
			$query = urldecode(str_replace('!translate ', '', implode(' ', $data->messageex)));
			$query = trim(str_replace($data->messageex[1], '', $query));
			$query = str_replace(' ', '+', $query);

			if (!isset($data->messageex[1])) {
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!translate lang_soure|lang_destination <query>` definendo la lingua di partenza e quella di destinazione. Per ulteriori informazioni `!man translate`.');
			} else {
				$this->talk($irc, $data,'http://translate.google.it/#' . $data->messageex[1] . '|' . $query);
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
			$this->talk($irc, $data, 'http://pastebin.com/ | http://paste.kde.org/ | http://nopaste.voric.com/ | http://notepad.cc/');
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
				$this->talk($irc, $data, 'Sintassi comando errata. Usa `!dado <n> <m>` dove n è il numero di dadi e m il numero di facce. Per ulteriori informazioni `!man dado`.');
			}
		}
	}

	/**
	 * Roulette russa.
	 *
	 * @return	event
	 */
	function roulette(&$irc, &$data)
	{
		if (!$this->flood($data)) {
			$nickname = $data->nick;
			$sbang = rand(0, 5);
			$sbam = rand(0, 5);

			$this->db_connect();

			if ($sbang == $sbam) {
				$query = "SELECT roulette_kick FROM bot_highscores WHERE nickname = '{$nickname}'";
				$result = mysql_query($query);

				if (mysql_num_rows($result) > 0) {
					$res = mysql_fetch_row($result);
					
					$sum = $res[0]+1;
					
					$query = "UPDATE bot_highscores SET roulette_kick = '{$sum}' WHERE nickname = '{$nickname}'";
					$result = mysql_query($query);
				} else {
					$query = "INSERT INTO bot_highscores (nickname, roulette_kick) VALUES ('{$nickname}', '1')";
					$result = mysql_query($query);
				}

				$irc->kick($data->channel, $data->nick, 'SBANG!');
			} else {
				$query = "SELECT roulette_survivor FROM bot_highscores WHERE nickname = '{$nickname}'";
				$result = mysql_query($query);

				if (mysql_num_rows($result) > 0) {
					$res = mysql_fetch_row($result);
					
					$sum = $res[0]+1;
					
					$query = "UPDATE bot_highscores SET roulette_survivor = '{$sum}' WHERE nickname = '{$nickname}'";
					$result = mysql_query($query);
				} else {
					$query = "INSERT INTO bot_highscores (nickname, roulette_survivor) VALUES ('{$nickname}', '1')";
					$result = mysql_query($query);
				}

				$this->talk($irc, $data, 'Sei salvo, per questa volta...');
			}
		}
	}

	/**
	 * Gioco a domande.
	 * IN DEVELOPMENT
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
					$res = mysql_fetch_assoc($result);

					$n = 1;

					$this->talk($irc, $data, 'Domanda #' . $n . ': ' . $res['question']);
					
					$this->trivia['answer'] = $res['answer'];
					
					$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, "/$this->trivia['answer']/i", $this, 'trivia_matching');
				}
			} else {
				$this->talk($irc, $data, 'Errore.');
			}
		}
	}

	/**
	 * Trova la risposta giusta nel Trivia.
	 * IN DEVELOPMENT
	 *
	 * @param	event
	 * @return	message
	 */
	function trivia_matching(&$irc, &$data)
	{
		$user = $data->nick;

		$this->talk($irc, $data, 'Vince ' . $user . '; Risposta: ' . $this->trivia['answer'] . '; Tempo impiegato: ' . $time . 's; Punti: ' . $score);

		$this->db_connect();

		$query = "SELECT trivia_score FROM bot_highscores WHERE nickname = '{$user}'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);

		if (mysql_num_rows($result) > 0) {
			$sum = $row[0]+1;
			$query = "UPDATE bot_highscores SET score = '{$sum}' WHERE nickname = '{$user}'";
			$result = mysql_query($query);
		} else {
			$query = "INSERT INTO bot_highscores (nickname, score) VALUES ('{$user}', '1')";
			$result = mysql_query($query);
		}
	}

	/**
	 * Permette di gestire il settaggio del Bot, come l'antiflood e i proverbi su citazione.
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
				
				case 'proverbi':
					if ($data->messageex[2] == 1) {
						$this->stop = TRUE;
						$this->talk($irc, $data, 'Adesso inizio a riempirvi di proverbi!');
					} elseif ($data->messageex[2] == 0) {
						$this->stop = FALSE;
						$this->talk($irc, $data, 'Mi sono stufato, per ora!');
					}
					break;

				default:
					break;
			}
		} else {
			$this->talk($irc, $data, 'Non sei autorizzato a darmi questo comando.');
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
			$this->talk($irc, $data, 'Non sei autorizzato a darmi questo comando.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!join <canale>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!part <canale>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!rejoin <canale>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!op <nickname>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!deop <nickname>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!voice <nickname>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!devoice <nickname>`.');
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
				
				array_shift($data->messageex);
				array_shift($data->messageex);
				$reason = implode(' ', $data->messageex);
				
				$channel = $data->channel;
				$irc->kick( $channel, $nickname, $reason);
			} else {
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!kick <nickname> <ragione>`.');
			}
		}
	}

	/**
	 * Effettua un kick random tra tutti gli utenti del canale ad esclusione del mandante del comando, il Bot ed il ChanServ.
	 * (Riservata agli operatori)
	 *
	 * @return	event
	 */
	function userslist_roulette(&$irc, &$data)
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

			$this->talk($irc, $data, 'Kick \'em all!');
			
			for ($i=5; $i > 0; $i--) { 
				$this->talk($irc, $data, '-' . $i);
				$irc->setSendDelay(800);
			}

			for ($i=0; $i < count($users_list); $i++) { 
				$irc->kick($data->channel, $users_list[$i], 'Clear!');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!mute <nickname>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!unmute <nickname>`.');
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
				$this->pvt_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!ban <nickname>`.');
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
				$this->priv_talk($irc, $data->nick, 'Sintassi comando errata. Usa `!unban <nickname>`.');
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
			$irc->quit('Bye bye!');
		} else {
			$this->talk($irc, $data, 'Non sei autorizzato a darmi questo comando.');
		}
	}
}