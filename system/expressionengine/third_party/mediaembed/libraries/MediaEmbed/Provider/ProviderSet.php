<?php

namespace MediaEmbed\Provider;

class ProviderSet
{
	private $_providers;

	function __construct($providers)
	{
		$this->_providers = $providers;
	}

	/**
	 * Lookup a provider by a code.
	 *
	 * @param $code string The code of the provider that we're looking for.
	 *
	 * @return Provider The provider with that code.
	 */
	public function getProvider($code)
	{
		foreach ($this->_providers as $provider)
		{
			if ($provider->code === $code)
			{
				return $provider;
			}
		}

		return null;
	}

	/**
	 * Test whether there is a provider in the set that can handle the URL
	 *
	 * @param $url string The url to find a provider for
	 *
	 * @return bool True if a provider can handle this URL, false if not.
	 */
	public function test($url)
	{
		return !is_null($this->find($url));
	}

	/**
	 * Find a provider that can handle the URL
	 *
	 * @param $url string The url to find a provider for
	 *
	 * @return Provider The first provider that can handle the URL, or null if
	 * none is found.
	 */
	public function find($url)
	{
		$result = null;

		foreach ($this->_providers as $provider)
		{
			if ($provider->test($url))
			{
				$result = $provider;
				break;
			}
		}

		return $result;
	}
}
