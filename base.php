<?php

/*
 * Object oriented wrapper of the sms77.de HTTP API
 *
 * (C) 2009 Michael Bemmerl
 *
 * This code is freely available under the BSD License.
 * (see http://creativecommons.org/licenses/BSD/)
 *
 * $Id$
 */

class Base
{
	public $User;
	public $Pass;
	public $UseSSL;
	private $host = 'gateway.sms77.de';
	protected $File = '';

	protected function ApiCall($data = array())
	{
		$data['u'] = $this->User;
		$data['p'] = md5($this->Pass);

		$query = http_build_query($data);
		$url = $this->buildUrl($query);

		return file_get_contents($url);
	}

	private function buildUrl($query)
	{
		$url = 'http';

		if ($this->UseSSL)
			$url .= 's';

		$url .= '://' . $this->host . '/';
		$url .= $this->File . '?' . $query;

		return $url;
	}
}

class ApiStatus
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
		return $this->GetMessage() . '(' . $this->Status . ')';
	}
}

?>