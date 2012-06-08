<?php

/*
 * Object oriented wrapper of the sms77.de HTTP API
 *
 * (C) 2009 Michael Bemmerl
 *
 * This code is freely available under the BSD License.
 * (see http://creativecommons.org/licenses/BSD/)
 *
 */

require_once('Base.class.php');

class Balance extends Base
{
	public function __construct()
	{
		parent::__construct();
		$this->HttpEngine->FilePath = 'balance.php';
	}

	public function Retrieve()
	{
		$response = $this->ApiCall();
		return new BalanceResult($response);
	}
}

class BalanceResult
{
	public $Status;
	public $Balance;

	public function __construct($response)
	{
		$this->Status = null;
		$this->Balance = null;

		if (strpos($response, '.') !== FALSE)
			$this->Balance = $response;
		else
			$this->Status = new ApiStatus($response);
	}

	public function __toString()
	{
		if ($this->Balance != null)
			return sprintf("%01.3f", $this->Balance) . ' €';
		else
			return $this->Status->__toString();
	}
}

?>