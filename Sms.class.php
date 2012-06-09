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

require_once('Base.class.php');

class Sms extends Base
{
	public $Text;
	public $To;
	public $From;
	public $Type;
	
	public $Delay;
	public $ReloadLock;

	public $Debug;
	public $Status;

	public function __construct()
	{
		parent::__construct();

		$this->Debug = FALSE;
		$this->Status = TRUE;
		$this->Delay = FALSE;
		$this->ReloadLock = TRUE;
	}

	private function checkIntegrity()
	{
		// check SMS text
		if (empty($this->Text))
			throw new BadMethodCallException('\'Text\' field not set.');
		if (strlen($this->Text) > 1555)
			throw new BadMethodCallException('\'Text\' field too long: 1555 chars is the maximum.');

		// check recipient
		if (empty($this->To))
			throw new BadMethodCallException('\'To\' field not set.');
		if (!is_numeric($this->To))
			throw new BadMethodCallException('\'To\' field has to be numerical.');

		// check desired SMS type
		if (empty($this->Type))
			throw new BadMethodCallException('\'Type\' field not set.');

		$class = new ReflectionClass('SmsType');
		$constants = $class->getConstants();
		$found = FALSE;

		// check if the supplied type is a valid SMS type.
		foreach($constants as $key => $value)
			if ($this->Type == $value)
			{
				$found = TRUE;
				break;
			}

		if (!$found)
			throw new UnexpectedValueException('Unknown value in \'Type\' field. Expecting one of the constants in the SmsType class.');

		// check sender
		if (empty($this->From))
		{
			if ($this->Type != SmsType::BASICPLUS && $this->Type != SmsType::BASICLOW)
				throw new BadMethodCallException('\'From\' field is not set, although SMS type does require a sender.');
		}
		else
		{
			if ($this->Type == SmsType::BASICPLUS || $this->Type == SmsType::BASICLOW)
				throw new BadMethodCallException('\'From\' field is set, although SMS type does not allow a custom sender.');
		}
		if (is_numeric($this->From) && strlen($this->From) > 16)
			throw new RangeException('Length of \'From\' number exceeded.');
		if (!is_numeric($this->From) && strlen($this->From) > 11)
			throw new RangeException('Length of \'From\' chars exceeded.');
	}

	private function parseDelayTime()
	{
		$human = strpos($this->Delay, '-');
		$datetime = $this->Delay;
		$now = time();

		if ($human !== FALSE)
			$datetime = strtotime($this->Delay);

		if ($datetime === FALSE || !is_numeric($datetime))
			throw new BadMethodCallException('Unrecognized date & time format for delayed delivery.');
		
		if ($now > $datetime)
			throw new BadMethodCallException('The date & time for delayed delivery is not in the future.');

		return $datetime;
	}

	public function Send()
	{
		$this->checkIntegrity();

		$data = array('to' => $this->To, 'text' => $this->Text, 'type' => $this->Type, 'return_msg_id' => 1);

		if ($this->From !== NULL)
			$data['from'] = $this->From;
		if ($this->Debug)
			$data['debug'] = 1;
		if ($this->Status)
			$data['status'] = 1;
		if ($this->Delay != FALSE)
			$data['delay'] = $this->parseDelayTime();
		if (!$this->ReloadLock)
			$data['no_reload'] = 1;

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
	const BASICLOW = 'basiclow';
	const BASICPLUS = 'basicplus';
	const QUALITY = 'quality';
	const FESTNETZ = 'festnetz';
	const FLASH = 'flash';
}

?>