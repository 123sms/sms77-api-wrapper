<?php

/*
 * Object oriented wrapper of the sms77.de HTTP API
 *
 * (C) 2009, 2012 Michael Bemmerl
 *
 * This code is freely available under the BSD License.
 * (see http://creativecommons.org/licenses/BSD/)
 *
 */

require_once('HttpEngine.class.php');

class Base
{
	public $Username;
	public $Password;
	public $UseSSL;

	protected $HttpEngine;

	public function __construct()
	{
		$this->HttpEngine = new HttpEngine();
		$this->HttpEngine->Host = 'gateway.sms77.de';
	}

	protected function ApiCall($data = array())
	{
		if (empty($this->Username))
			throw new BadMethodCallException('Username not set.');
		if (empty($this->Password))
			throw new BadMethodCallException('Password not set.');

		$data['u'] = $this->Username;
		$data['p'] = $this->getApiPassword();

		if ($this->UseSSL && !$this->HttpEngine->WrapperAvailable('https'))
			throw new BadMethodCallException('\'UseSSL\' set, but SSL not available on this system.');

		$this->HttpEngine->UseSSL = $this->UseSSL;
		$result = $this->HttpEngine->GetRequest($data);

		if ($result === FALSE)
			throw new ErrorException('The API was not reachable.');
		else
			return $this->HttpEngine->Response;
	}

	private function getApiPassword()
	{
		$password = $this->Password;

		// detect if the user already specified an md5 hash
		if (preg_match('#^[0-9a-f]{32}$#i', $this->Password) == 0)
			$password = md5($password);

		return $password;
	}
}

final class ApiStatus
{
	const OK = 100;
	const SOME_RECIPIENTS_FAILED = 101;
	const COUNTRYCODE_INVALID = 201;
	const RECIPIENT_NUMBER_INVALID = 202;
	const AUTHORIZATION_MISSING = 300;
	const to_PARAMETER_MISSING = 301;
	const type_PARAMETER_MISSING = 304;
	const text_PARAMETER_MISSING = 305;
	const SENDER_NUMBER_INVALID = 306;
	const url_PARAMETER_MISSING = 307;
	const INVALID_TYPE = 400;
	const text_PARAMETER_TOO_LONG = 401;
	const RELOAD_LOCK = 402;
	const NOT_ENOUGH_CREDIT = 500;
	const CARRIER_FAILED = 600;
	const UNKNOWN_ERROR = 700;
	const LOGOFILE_MISSING = 801;
	const LOGOFILE_NOT_EXISTENT = 802;
	const RINGTONE_MISSING = 803;
	const WRONG_AUTHORIZATION = 900;
	const WRONG_MESSAGE_ID = 901;
	const API_DEACTIVATED = 902;
	const WRONG_IP = 903;

	private $Status;

	public function __construct($status)
	{
		$this->Status = $status;
	}

	public function GetMessage()
	{
		$class = new ReflectionClass('ApiStatus');
		$constants = $class->getConstants();

		foreach($constants as $key => $value)
			if ($value == $this->Status)
				return $key;

		return 'UNKOWN_STATUS';
	}

	public function __toString()
	{
		return $this->GetMessage() . ' (' . $this->Status . ')';
	}
}

?>