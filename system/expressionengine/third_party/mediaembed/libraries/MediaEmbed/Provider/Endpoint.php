<?php

namespace MediaEmbed\Provider;

class Endpoint
{
	public $href;
	public $type;

	function __construct($href, $type)
	{
		$this->href = $href;
		$this->type = $type;
	}
}
