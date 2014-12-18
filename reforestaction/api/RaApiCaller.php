<?php

if (!defined('_PS_VERSION_'))
	exit;

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'ApiCaller.php';

class RaApiCaller extends ApiCaller
{

	const PUBLIC_KEY = 'a';

	protected $debug = true;

	protected function getBody(array $fields)
	{
		$return = true;

		// if fields not empty
		if (empty($fields))
			$return = false;

		// if not empty
		if ($return)
		{
			// it not an array
			if (!is_array($fields))
				$fields = array($fields);

			// Build str
			if (extension_loaded('pecl_http'))
				$return = http_build_str($fields);
			else
			{
				$options = '';
				foreach ($fields as $key => $field)
					$options[] = $key.'='.urlencode($field);

				$return = implode('&', $options);
			}
		}

		return $return;
	}

	/**
	 * Get the merchant status
	 * @return mixed Call response
	 */
	public function getStatus()
	{
		$this->verb = 'GET';
		$this->endpoint = 'merchants/'.Configuration::get('RA_MERCHANT_ID').'/status?merchant_key='.Configuration::get('RA_MERCHANT_KEY').'&rand='.rand(0, 100);

		$this->makeCall();

		return $this->_response;
	}

	/**
	 * Create merchant account
	 * @param  array  $fields Fields to post
	 * @return mixed Call response
	 */
	public function createAccount($fields)
	{
		$this->verb = 'POST';
		$this->endpoint = 'merchants';

		$this->makeCall($this->getBody($fields));

		return $this->_response;
	}

	/**
	 * Send orders
	 * @param  array  $fields Fields to post
	 * @return mixed          Call response
	 */
	public function sendOrder(array $fields)
	{
		$this->verb = 'POST';
		$this->endpoint = 'merchants/'.Configuration::get('RA_MERCHANT_ID').'/orders?rand='.rand(0, 10);

		$this->makeCall($this->getBody($fields));

		return $this->_response;
	}

	/**
	 * Overide for add public key
	 * @param  mixed  $body        body content
	 * @param  mixed  $http_header header content
	 * @param  string $user        User login
	 * @param  string $passwd      User password
	 */
	protected function makeCall($body = null, $http_header = null, $user = null, $passwd = null)
	{

		if (is_null($http_header) || !is_array($http_header))
			$http_header = array($http_header);

		$http_header = array_merge(array('PUBLIC_KEY: '.self::PUBLIC_KEY), $http_header);

		parent::makeCall($body, $http_header, $user, $passwd);
	}
}

?>