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

require_once('Base.class.php');

class Mms extends Base
{
	public $Text;
	public $To;
	public $From;
	public $File1;
	public $File2;
	public $File3;
	
	public $Delay;
	public $ReloadLock;

	public $Debug;
	public $Status;

	public function __construct()
	{
		parent::__construct();

		$this->HttpEngine->FilePath = 'media.php';

		$this->Debug = FALSE;
		$this->Status = TRUE;
	}

	private function checkIntegrity()
	{
		// check SMS text
		$this->checkTextIntegrity();

		// check recipient
		if (empty($this->To))
			throw new BadMethodCallException('\'To\' field not set.');
		if (!is_numeric($this->To))
			throw new RangeException('\'To\' field has to be numerical.');

		// check sender
		if (empty($this->From))
			throw new BadMethodCallException('\'From\' field not set.');
		if (is_numeric($this->From) && strlen($this->From) > 16)
			throw new RangeException('Length of \'From\' number exceeded.');
		if (!is_numeric($this->From) && strlen($this->From) > 11)
			throw new RangeException('Length of \'From\' chars exceeded.');

		// check data files
		$this->checkFiles();
	}

	private function checkTextIntegrity()
	{
		if (empty($this->Text))
			throw new BadMethodCallException('\'Text\' field not set.');
		if (strlen($this->Text) > 500)
			throw new RangeException('\'Text\' field too long: 1555 chars is the maximum.');
	}

	private function checkFiles()
	{
		if ($this->File1 === NULL && $this->File2 === NULL && $this->File3 === NULL)
			throw new BadMethodCallException('No \'File\' fields set.');
	}

	public function Send()
	{
		$this->checkIntegrity();

		$data = array('to' => $this->To, 'text' => $this->Text, 'from' => $this->From, 'type' => 'mms', 'return_msg_id' => 1);

		if ($this->File1 !== NULL)
			$data['file1'] = $this->File1;
		if ($this->File2 !== NULL)
			$data['file2'] = $this->File2;
		if ($this->File3 !== NULL)
			$data['file3'] = $this->File3;

		if ($this->Debug)
			$data['debug'] = 1;
		if ($this->Status)
			$data['status'] = 1;

		$response = $this->ApiCall($data);

		return new MmsResult($response);
	}
}

class MmsResult
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
