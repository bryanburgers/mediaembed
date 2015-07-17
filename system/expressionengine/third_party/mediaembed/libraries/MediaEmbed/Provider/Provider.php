<?php

namespace MediaEmbed\Provider;

use \Exception;
use MediaEmbed\Provider\Result;

class Provider
{
	public $code;
	public $name;
	public $endpoints;
	public $schemes;
	public $userAgent = 'MediaElement/1.0';

	function __construct($code, $name, $endpoints, $schemes)
	{
		$this->code = $code;
		$this->name = $name;
		$this->endpoints = $endpoints;
		$this->schemes = $schemes;
	}

	public function fetch($url, $parameters = null)
	{
		if (!$this->test($url)) {
			throw new Exception("Provider '{$this->code}' does not support url '{$url}'.");
		}

		// Why are there multiple endpoints? Always use the first one.
		if (count($this->endpoints) < 1) {
			throw new Exception("Provider '{$this->code}' has no endpoints.");
		}

		$endpoint = $this->endpoints[0];

		$data = null;

		$oembedUrl = $this->buildFetchUrl($endpoint, $url, $parameters);

		switch ($endpoint->type) {
			case 'application/json+oembed';
				$data = $this->fetchJSON($oembedUrl);
				break;
/*
			case 'text/xml+oembed';
				$data = $this->fetchXML($oembedUrl);
				break;
*/
			default:
				throw new Exception("Provider '{$this->code}', first endpoint has an invalid type '{$endpoint->type}'.");
		}

		return new Result($url, $this->code, $data);
	}

	private function buildFetchUrl($endpoint, $url, $parameters)
	{
		$base = $endpoint->href;

		$hasQuery = !is_null(parse_url($base, PHP_URL_QUERY));
		$separator = $hasQuery ? '&' : '?';

		$base .= $separator . 'url=' . urlencode($url);

		if (!is_null($parameters))
		{
			foreach ($parameters as $key => $value)
			{
				$base .= '&' . urlencode($key) . '=' . urlencode($value);
			}
		}

		return $base;
	}

	private function fetchData($url, $contentType)
	{
		$s = curl_init();

		curl_setopt($s, CURLOPT_URL, $url);
		curl_setopt($s, CURLOPT_HTTPHEADER, array(
			"Accept: {$contentType}, */*; 0.2"
			));
		curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($s, CURLOPT_TIMEOUT, 3);
		curl_setopt($s, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

		// OH NO! THIS IS TERRIBLE!
		curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($s, CURLOPT_USERAGENT, $this->userAgent);

		$data = curl_exec($s);
		if(!curl_errno($s))
		{
			$info = curl_getinfo($s);
			$code = curl_getinfo($s, CURLINFO_HTTP_CODE);

			if ($code !== 200)
			{
				throw new Exception('Fetch returned status code ' . $code);
			}
		}
		else
		{
			throw new Exception('Curl error: ' . curl_error($s));
		}

		curl_close($s);

		return $data;
	}

	private function fetchJSON($url)
	{
		$data = $this->fetchData($url, 'application/json+oembed');
		return json_decode($data);
	}

/*
	private function fetchXML($endpoint, $url, $parameters)
	{
		$data = $this->fetchData($url, 'application/json+oembed');
		// Decode XML here
	}
*/

	public function test($url)
	{
		$result = false;

		foreach ($this->schemes as $scheme)
		{
			if ($scheme->test($url))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}
}
