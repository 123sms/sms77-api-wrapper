<?php

/*
 * Object oriented wrapper of the sms77.de HTTP API
 *
 * (C) 2009 Michael Bemmerl
 *
 * This code is freely available under the BSD License.
 * (see http://creativecommons.org/licenses/BSD/)
 *
 * $Id: Status.class.php 9 2009-12-22 22:56:47Z google@mx-server.de $
 */

class HttpEngine
{
	public $Host;
	public $FilePath;
	public $UseSSL;
	public $ArgumentSeparator;

	public $Response;

	public function __construct()
	{
		$this->Host = null;
		$this->FilePath = '';
		$this->UseSSL = FALSE;
		$this->ArgumentSeparator = '&';

		$this->Response = null;
	}
	
	public function GetRequest($arguments = array())
	{
		if (empty($this->Host))
			throw new BadMethodCallException('Host field not set.');

		$query = http_build_query($arguments, '', $this->ArgumentSeparator);
		$url = $this->buildUrl($query);

		$this->Response = null;
		$response = @file_get_contents($url);

		// did the request succeed?
		if ($response !== FALSE)
		{
			$this->Response = $response;
			return TRUE;
		}
		else
			return FALSE;
	}

	private function buildUrl($query = null)
	{
		$url = 'http';

		if ($this->UseSSL)
			$url .= 's';

		$filePath = $this->FilePath;

		// remove slash in the beginning
		if (!empty($filePath) && $filePath[0] == '/')
			$filePath = substr($filePath, 1);

		$url .= '://' . $this->Host . '/' . $filePath;

		if (!empty($query))
			$url .= '?' . $query;

		return $url;
	}
}