<?php

namespace MediaEmbed\Provider;

class Result
{
	public $provider;
	public $data;

	function __construct($provider, $data)
	{
		$this->provider = $provider;
		$this->data = $data;
	}
}
