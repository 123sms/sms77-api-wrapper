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

require_once('base.php');

class Sms extends Base
{
	public $Text;
	public $To;
	public $From;
	public $Type;

	public $Debug;
	public $Status;

	public function __construct()
	{
		$this->Debug = FALSE;
		$this->Status = TRUE;
	}

	private function checkIntegrity()
	{
		if (empty($this->Text))
			throw new LogicException('Text not set.');

		if (empty($this->To))
			throw new LogicException('To not set.');
		if (!is_numeric($this->To))
			throw new LogicException('To has to be numerical.');

		if (empty($this->Type))
			throw new LogicException('Type not set.');

		if (empty($this->From) && $this->Type != SmsType::BASICPLUS)
			throw new LogicException('From is not set, although SMS type is not BasicPlus.');
		if (is_numeric($this->From) && strlen($this->From) > 16)
			throw new LogicException('Length of From number exceeded.');
		if (!is_numeric($this->From) && strlen($this->From) > 11)
			throw new LogicException('Length of From chars exceeded.');
	}

	public function Send()
	{
		$this->checkIntegrity();

		$data = array('to' => $this->To, 'text' => $this->Text, 'type' => $this->Type, 'return_msg_id' => 1);

		if ($this->Debug)
			$data['debug'] = 1;
		if ($this->Status)
			$data['status'] = 1;

		$response = $this->ApiCall($data);

		return new SmsResult($response);
	}
}

class SmsResult
{
	public $Status;
	public $MessageId;

	public function __construct($response)
	{
		$data = explode("\n", $response);

		$this->Status = new ApiStatus($data[0]);

		if (count($data) > 1)
			$this->MessageId = $data[1];
		else
			$this->MessageId = null;
	}
}

class SmsType
{
	const BASICPLUS = 'basicplus';
	const QUALITY = 'quality';
	const FESTNETZ = 'festnetz';
	const FLASH = 'flash';
}

?>