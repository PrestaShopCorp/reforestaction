<?php

if (!defined('_PS_VERSION_'))
	exit;

abstract class ApiCaller
{
	/**
	 *  Host of call
	 * @var string
	 */
	protected $host;

	/**
	 * Endpoint
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Instance of module who use this class
	 * @var Module
	 */
	protected $module;

	/**
	 * Response of call
	 * @var string
	 */
	protected $_response;

	/**
	 * Only POST, GET for the moment
	 * @var string
	 */
	protected $action;

	/**
	 * Logs
	 * @var array
	 */
	protected $_logs = array();

	/**
	 * If debug mod
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Initialization
	 */
	public function __construct($host, Module $module)
	{
		$this->host     = $host;
		$this->module   = $module;
	}

	/**
	 * Get body
	 * @return mixed Create body
	 */
	abstract protected function getBody(array $fields);

	/**
	 * Make call
	 * @param  mixed  $body        body content
	 * @param  mixed  $http_header header content
	 * @param  string $user        User login
	 * @param  string $passwd      User password
	 * @return mixed               Call
	 */
	protected function makeCall($body = null, $http_header = null, $user = null, $passwd = null)
	{
		// init uri to call
		$uri_to_call = $this->host.$this->endpoint;

		$this->_logs[] = '======================================================================================================';
		$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Making new connection to :').' '.$uri_to_call;

		// Init for cURL
		if (extension_loaded('curl'))
			$this->_response = $this->connectByCurl($uri_to_call, $http_header, $body, $user, $passwd);
		// Init for fsock
		else if (function_exists('fsockopen'))
			$this->_response = $this->connectByFsock($uri_to_call, $http_header, $body, $user, $passwd);
		else
		{
			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Call is not possible, cURL and fsockopen is not enable.');
			$this->_response = false;
		}

		$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.'Response : '.$this->_response;

		if ($this->_response != false)
			$this->_response = Tools::jsonDecode($this->_response);
		
		$this->_logs[] = '======================================================================================================';


		if ($this->debug)
			$this->writeLog();
	}

	/**
	 * Exec by cURL
	 * @return mixed Content receive
	 */
	private function connectByCurl($uri_to_call, $http_header, $body, $user, $passwd)
	{
		// init connection
		$ch = curl_init($uri_to_call);
		// init result
		$result = false;

		if ($ch)
		{
			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Connect with cURL successfull');

			// Connection with user
			if (!is_null($user))
			{
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Use identifier to connect:').' '.$user;
				$connection_string = $user;

				// if use password
				if (!is_null($passwd))
				{
					$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Use password to connect');
					$connection_string .= ':'.$passwd;
				}

				@curl_setopt($ch, CURLOPT_USERPWD, $connection_string);
			}

			// use header if necessary
			if (is_array($http_header) && count($http_header))
			{
				@curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.'Headers : '.Tools::jsonEncode($http_header);
			}

			// if want post
			if ($this->action == 'POST')
				@curl_setopt($ch, CURLOPT_POST, true);

			// if body if necessary
			if ($body)
			{
				@curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.'Body : '.$body;
			}

			@curl_setopt($ch, CURLOPT_HEADER, false);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			@curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			@curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			@curl_setopt($ch, CURLOPT_VERBOSE, false);

			// exec call
			$result = curl_exec($ch);

			if (!$result)
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Send failed');
			else
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Send successfull');
		}
		else
			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Connect failed with cURL');

		return $result;
	}

	/**
	 * Exec by fsockopen
	 */
	private function connectByFsock($uri_to_call, $http_header, $body, $user, $passwd)
	{
		// Initialize temporary content
		$tmp = false;

		// Open socket
		$handle = @fsockopen($uri_to_call, -1, $errno, $errmsg);

		if ($handle)
		{
			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Connect with fsockopen successfull');
			$headers = $this->_makeHeader(Tools::strlen($body), $http_header);
			// Puts
			@fwrite($handle, $headers.$body);

			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.'Headers : '.$headers;
			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.'Body : '.$body;

			// Read lines
			$tmp = '';

			while (!feof($handle))
				$tmp .= trim(fgets($handle, 1024));

			// Close handle
			@fclose($handle);

			if ($tmp == '')
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Send failed');
			else
				$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Send successfull');
		}
		else
			$this->_logs[] = '['.strftime('%Y-%m-%d %H:%M:%S').'] : '.$this->module->l('Connect failed with fsockopen').' '.$errmsg	;

		return $tmp;
	}

	/**
	 * Make Header
	 * @return string Header
	 */
	private function _makeHeader($body_length, $http_header)
	{
		$header = 'POST '.(string)$this->endpoint.' HTTP/1.1'."\r\n".
					'Host: '.(string)$this->host."\r\n".
					'Content-Type: application/x-www-form-urlencoded'."\r\n".
					'Content-Length: '.(int)$body_length."\r\n".
					'Connection: close'."\r\n";

		if (is_array($http_header) && count($http_header))
		{
			foreach ($http_header as $v)
				$header .= $v."\r\n";
		}

		$header .= "\r\n";

		return $header;
	}

	/**
	 * Get Logs
	 * @return array Logs
	 */
	final public function getLogs()
	{
		return $this->_logs;
	}

	final protected function writeLog()
	{

		if (!$this->debug)
			return false;

		$handle = fopen(dirname(__FILE__) . '/../log.txt', 'a+');

		foreach ($this->getLogs() as $value)
			fwrite($handle, $value."\r");

		$this->_logs = array();

		fclose($handle);
	}

}

?>