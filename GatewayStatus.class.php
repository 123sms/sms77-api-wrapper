<?php

/*
 * Object oriented wrapper of the sms77.de HTTP API
 *
 * (C) 2012 Michael Bemmerl
 *
 * This code is freely available under the BSD License.
 * (see http://creativecommons.org/licenses/BSD/)
 *
 */

require_once('HttpEngine.class.php');

class GatewayStatus extends Base
{
	public function __construct()
	{
		parent::__construct();

		$this->NeedsAuthentication = FALSE;
		$this->HttpEngine->FilePath = 'gateway-status.php';
	}

	public function Retrieve()
	{
		$response = $this->ApiCall();

		if (empty($response))
			throw new ErrorException('The API returned an empty response.');

		return $this->parseXml($response);
	}

	private function parseXml($xmlData)
	{
		$xml = new SimpleXMLElement($xmlData);
		$result = array();

		foreach($xml as $gateway)
		{
			$delay = new GatewayDelay($gateway->getName());
			$delay->D1 = (integer) $gateway['d1'];
			$delay->D2 = (integer) $gateway['d2'];
			$delay->O2 = (integer) $gateway['o2'];
			$delay->EPlus = (integer) $gateway['eplus'];

			$result[] = $delay;
		}

		return $result;
	}
}

class GatewayDelay
{
	public $EPlus;
	public $D1;
	public $D2;
	public $O2;

	public $SmsType;

	public function __construct($smsType)
	{
		$this->SmsType = strtolower($smsType);
	}
}
