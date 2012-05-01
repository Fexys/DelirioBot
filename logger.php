<?php
error_reporting (E_ALL);

include('SmartIRC.php');

class DelirioLogger
{
	function run(&$irc)
	{
		//File di configurazione
		require_once('config.php');
		foreach ($config['logger'] as $key => $value) {
			$this->config[$key] = $value;
		}

		//Setup di SmartIRC
		$irc->setDebug(SMARTIRC_DEBUG_ALL);
		$irc->setLogfile(LOG_DIR . 'debug_logger.log');
		$irc->setLogdestination(SMARTIRC_FILE);
		$irc->setUseSockets(TRUE);
		$irc->setUserSyncing(TRUE);
		$irc->setChannelSyncing(TRUE);
		$irc->setAutoRetry(TRUE);
		$irc->setAutoReconnect(TRUE);
		$irc->setReceiveTimeout(6000);
		$irc->setTransmitTimeout(6000);
		$irc->setCtcpVersion($this->config['nickname'] . ' [ver ' . VERSION . ']');
		$irc->setSendDelay(500);

		$irc->connect($this->config['server'], $this->config['port']);
		$irc->login($this->config['nickname'], $this->config['realname'], $this->config['usermode'], $this->config['username'], $this->config['password']);
		$irc->join($this->config['join']);
		if (LOGGER) {
			$this->startlog($irc);
		}
		$irc->listen();
		$irc->disconnect();
		if (LOGGER) {
			$this->stoplog();
		}
	}

	function startlog(&$irc)
	{
		$irc->registerActionhandler(
			SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_NOTICE|SMARTIRC_TYPE_JOIN|
			SMARTIRC_TYPE_ACTION|SMARTIRC_TYPE_TOPICCHANGE|SMARTIRC_TYPE_NICKCHANGE|
			SMARTIRC_TYPE_QUIT|SMARTIRC_TYPE_PART, '.*', $this, 'log2file');

		$year = date('Y');
		$month = date('F');
		$day = date('d');

		$file_log = LOG_DIR . $year . '/' . $month . '/' . $day . '.log';

		$this->log = fopen($file_log, 'a');
	}

	function stoplog()
	{
		fclose($this->log);
	}

	function log2file(&$irc, &$data)
	{
		$now = date('[H:i:s]');

		switch ($data->type) {
			case SMARTIRC_TYPE_CHANNEL:
				$line = $data->nick . ': ' . $data->message;
				break;

			case SMARTIRC_TYPE_CTCP_REQUEST:
				$line = $data->nick . ': ' . $data->message;
				break;

			case SMARTIRC_TYPE_CTCP_REPLY:
				$line = $data->nick . ': ' . $data->message;
				break;

			case SMARTIRC_TYPE_CTCP:
				$line = $data->nick . ': ' . $data->message;
				break;

			case SMARTIRC_TYPE_ACTION:
				$line = '* ' . $data->nick . substr($data->message, 7);
				break;

			case SMARTIRC_TYPE_NICKCHANGE:
				$line = '*** ' . $data->nick . ' is now known as ' . $data->message;
				break;

			case SMARTIRC_TYPE_TOPICCHANGE:
				$line = '*** ' . $data->nick . ' changed topic to ' . $data->message;
				break;

			case SMARTIRC_TYPE_JOIN:
				$line = '*** Joined ' . $data->nick . ' (' . $data->ident . '@' . $data->host . ')';
				break;

			case SMARTIRC_TYPE_PART:
				$line = '*** Parted ' . $data->nick . ' (' . $data->ident . '@' . $data->host . ')';
				break;

			case SMARTIRC_TYPE_QUIT:
				$line = '*** ' . $data->nick . ' quit: ' .$data->message;
				break;

			default:
				$line = $data->rawmessage;
		}

		if ($data->channel) {
			fwrite($this->log, "$now $line\n");
		}
	}
}