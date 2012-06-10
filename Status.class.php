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

class Status extends Base
{
	public $MessageId;

	public function __construct()
	{
		parent::__construct();
		$this->HttpEngine->FilePath = 'status.php';
	}

	public function Retrieve()
	{
		if (empty($this->MessageId))
			throw new BadMethodCallException('MessageId not set.');

		$data = array('msg_id' => $this->MessageId);

		$response = $this->ApiCall($data);
		return SmsStatus::Parse($response);
	}
}

class SmsStatus
{
	const UNKNOWN_STATUS = -2;
	const NO_STATUS = -1;
	const NONE = 0;
	const TRANSMITTED = 1;
	const DELIVERED = 2;
	const NOTDELIVERED = 3;
	const BUFFERED = 4;

	public $Status;
	public $SmsStatus;
	public $Time;

	public function __construct()
	{
		$this->Status = null;
		$this->SmsStatus = null;
		$this->Time = null;
	}
	
	public static function Parse($response)
	{
		$smss = new SmsStatus();

		if (strlen($response) == 3)
			$smss->Status = new ApiStatus($response);
		else
		{
			$data = explode("\n", $response);

			$smss->Time = new DateTime(date(DATE_ATOM,$data[1]));
			$smss->SmsStatus = SmsStatus::UNKNOWN_STATUS;

			$class = new ReflectionClass('SmsStatus');
			$constants = $class->getConstants();

			foreach($constants as $key => $value)
			{
				if ($key == $data[0])
				{
					$smss->SmsStatus = $value;
					break;
				}
			}

		}

		return $smss;
	}

	public function GetMessage()
	{
		$class = new ReflectionClass('SmsStatus');
		$constants = $class->getConstants();

		foreach($constants as $key => $value)
			if ($value == $this->SmsStatus)
				return $key;

		return 'UNKOWN_STATUS';
	}

	public function __toString()
	{
		if ($this->Status != null)
			return $this->Status->__toString();
		else
			return $this->GetMessage() . ', at ' . $this->Time->format(DATE_RFC822);
	}
}
