<?php

namespace MediaEmbed\Provider;

use \stdClass;

class Result
{
	protected $_originalUrl;
	protected $_providerCode;

	function __construct($url, $providerCode, $data)
	{
		$this->_originalUrl = $url;
		$this->_providerCode = $providerCode;

		foreach (get_object_vars($data) as $key => $value)
		{
			$this->{$key} = $value;
		}
	}

	public function getOriginalUrl()
	{
		return $this->_originalUrl;
	}

	public function getProviderCode()
	{
		return $this->_providerCode;
	}

	public function toSerializableObject()
	{
		$obj = new stdClass();
		$obj->{'mediaembed:original_url'} = $this->getOriginalUrl();
		$obj->{'mediaembed:provider_code'} = $this->getProviderCode();

		foreach (get_object_vars($this) as $key => $value)
		{
			// If the key starts with '_', then it's a private variable and we
			// do not want it in the serialized object.
			if (strpos($key, '_') === 0)
			{
				continue;
			}
			$obj->{$key} = $value;
		}

		return $obj;
	}

	public static function parseJSON($json)
	{
		try
		{
			$obj = json_decode($json);
		}
		catch (Exception $e)
		{
			return null;
		}

		if (is_null($obj))
		{
			return null;
		}

		$url = $obj->{'mediaembed:original_url'};
		$providerCode = $obj->{'mediaembed:provider_code'};

		unset($obj->{'mediaembed:original_url'});
		unset($obj->{'mediaembed:provider_code'});

		return new Result($url, $providerCode, $obj);
	}
}
