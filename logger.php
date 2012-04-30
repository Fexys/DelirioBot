<?php

class DelirioLogger
{
	function startlog(&$irc)
	{
		$irc->registerActionhandler(
			SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_NOTICE|SMARTIRC_TYPE_JOIN|
			SMARTIRC_TYPE_ACTION|SMARTIRC_TYPE_TOPICCHANGE|SMARTIRC_TYPE_NICKCHANGE|
			SMARTIRC_TYPE_QUIT|SMARTIRC_TYPE_PART, '.*', $this, 'log2file');

		$year = date('Y');
		$month = date('F');
		$day = date('d');

		$file_log = LOG_DIR . '/' . $year . '/' . $month . '/' . $day . '.log';

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