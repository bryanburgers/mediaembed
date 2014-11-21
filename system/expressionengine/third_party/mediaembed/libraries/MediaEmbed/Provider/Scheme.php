<?php

namespace MediaEmbed\Provider;

class Scheme
{
	public $pattern;

	function __construct($pattern)
	{
		$this->pattern = $pattern;
	}

	public function test($url)
	{
		$regex = preg_quote($this->pattern, '/');
		$regex = str_replace('\*', '.*', $regex);
		$regex = '/^' . $regex . '$/';
		return (bool)preg_match($regex, $url);
	}
}
