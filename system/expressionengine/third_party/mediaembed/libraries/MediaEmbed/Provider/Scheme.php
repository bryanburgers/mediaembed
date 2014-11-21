<?php

namespace MediaEmbed\Provider;

class Scheme
{
	public $value;

	function __construct($value)
	{
		$this->value = $value;
	}

	public function test($url)
	{
		$regex = preg_quote($this->value, '/');
		$regex = str_replace('\*', '.*', $regex);
		$regex = '/^' . $regex . '$/';
		return (bool)preg_match($regex, $url);
	}
}
