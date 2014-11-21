<?php

namespace MediaEmbed\Provider;

use MediaEmbed\Provider\Endpoint;
use MediaEmbed\Provider\Provider;
use MediaEmbed\Provider\Scheme;

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

	/**
	 * Get all of the loaded providers as an array.
	 */
	public function getAll()
	{
		return $this->_providers;
	}

	/**
	 * Load a providers.xml file
	 *
	 * @param $file string The file path
	 *
	 * @return ProviderSet The parsed providers.
	 */
	public static function load($filename)
	{
		$contents = file_get_contents($filename);

		$providersEl = new \SimpleXMLElement($contents);

		$providers = array();

		foreach ($providersEl->provider as $providerEl)
		{
			$providers[] = ProviderSet::loadProvider($providerEl);
		}

		return new ProviderSet($providers);
	}

	protected static function loadProvider($providerEl)
	{
		$code = (string)$providerEl['code'];
		$name = (string)$providerEl['name'];

		$endpoints = array();
		$schemes = array();

		foreach ($providerEl->endpoints->endpoint as $endpointEl)
		{
			$endpoints[] = ProviderSet::loadEndpoint($endpointEl);
		}

		foreach ($providerEl->schemes->scheme as $schemeEl)
		{
			$schemes[] = ProviderSet::loadScheme($schemeEl);
		}

		return new Provider($code, $name, $endpoints, $schemes);
	}

	protected static function loadEndpoint($endpointEl)
	{
		$href = (string)$endpointEl['href'];
		$type = (string)$endpointEl['type'];

		return new Endpoint($href, $type);
	}

	protected static function loadScheme($schemeEl)
	{
		$pattern = (string)$schemeEl['pattern'];

		return new Scheme($pattern);
	}
}
