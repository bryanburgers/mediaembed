<?php

require_once 'system/expressionengine/third_party/mediaembed/libraries/autoload.php';

use \MediaEmbed\Provider\Endpoint;
use \MediaEmbed\Provider\Scheme;
use \MediaEmbed\Provider\Provider;

class ProviderTest extends PHPUnit_Framework_TestCase
{
	public function testTest()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');

		$provider = new Provider('twitter', 'Twitter', array(), array($httpTwitter, $httpsTwitter));

		$this->assertTrue($provider->test('https://twitter.com/ee_blocks/status/516919035087159296'));
		$this->assertTrue($provider->test('http://twitter.com/ee_blocks/status/516919035087159296'));
		$this->assertFalse($provider->test('https://instagram.com/p/1'));
		$this->assertFalse($provider->test('http://instagram.com/p/1'));
	}

	/**
	 * @expectedException Exception
	 */
	public function testFetchNoEndpoint()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');

		$provider = new Provider('twitter', 'Twitter', array(), array($httpTwitter, $httpsTwitter));

		$result = $provider->fetch('https://twitter.com/bryanburgers/status/532242786162995200');
	}

	/**
	 * @expectedException Exception
	 */
	public function testFetchInvalidUrl()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');
		$endpoint = new Endpoint('https://api.twitter.com/1/statuses/oembed.json', 'application/json+oembed');

		$provider = new Provider('twitter', 'Twitter', array($endpoint), array($httpTwitter, $httpsTwitter));

		$result = $provider->fetch('https://burgers.io/');
	}

	public function testFetch()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');
		$endpoint = new Endpoint('https://api.twitter.com/1/statuses/oembed.json', 'application/json+oembed');

		$provider = new Provider('twitter', 'Twitter', array($endpoint), array($httpTwitter, $httpsTwitter));

		$result = $provider->fetch('https://twitter.com/bryanburgers/status/532242786162995200');

		$this->assertNotNull($result);

		$this->assertObjectHasAttribute('provider', $result);
		$this->assertEquals($result->provider, $provider);

		$this->assertObjectHasAttribute('data', $result);
		$this->assertObjectHasAttribute('html', $result->data);
		$this->assertEquals($result->data->type, 'rich');
		$this->assertEquals($result->data->author_url, 'https://twitter.com/bryanburgers');
	}

	public function testFetchWithUrlParameter()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');
		$endpoint = new Endpoint('https://api.twitter.com/1/statuses/oembed.json?lang=en', 'application/json+oembed');

		$provider = new Provider('twitter', 'Twitter', array($endpoint), array($httpTwitter, $httpsTwitter));

		$result = $provider->fetch('https://twitter.com/bryanburgers/status/532242786162995200');

		$this->assertNotNull($result);

		$this->assertObjectHasAttribute('provider', $result);
		$this->assertEquals($result->provider, $provider);

		$this->assertObjectHasAttribute('data', $result);
		$this->assertObjectHasAttribute('html', $result->data);
		$this->assertEquals($result->data->type, 'rich');
		$this->assertEquals($result->data->author_url, 'https://twitter.com/bryanburgers');
	}

	public function testFetchWithParameters()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');
		$endpoint = new Endpoint('https://api.twitter.com/1/statuses/oembed.json', 'application/json+oembed');

		$provider = new Provider('twitter', 'Twitter', array($endpoint), array($httpTwitter, $httpsTwitter));

		$parameters = array('lang' => 'en');

		$result = $provider->fetch('https://twitter.com/bryanburgers/status/532242786162995200', $parameters);

		$this->assertNotNull($result);

		$this->assertObjectHasAttribute('provider', $result);
		$this->assertEquals($result->provider, $provider);

		$this->assertObjectHasAttribute('data', $result);
		$this->assertObjectHasAttribute('html', $result->data);
		$this->assertEquals($result->data->type, 'rich');
		$this->assertEquals($result->data->author_url, 'https://twitter.com/bryanburgers');
	}

	/**
	 * @expectedException Exception
	 */
	public function testFetch404()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');
		$endpoint = new Endpoint('https://api.twitter.com/1/statuses/oembed.json', 'application/json+oembed');

		$provider = new Provider('twitter', 'Twitter', array($endpoint), array($httpTwitter, $httpsTwitter));

		$result = $provider->fetch('https://twitter.com/bryanburgers/status/1');

		$this->assertNotNull($result);

		$this->assertObjectHasAttribute('provider', $result);
		$this->assertEquals($result->provider, $provider);

		$this->assertObjectHasAttribute('data', $result);
		$this->assertObjectHasAttribute('html', $result->data);
		$this->assertEquals($result->data->type, 'rich');
		$this->assertEquals($result->data->author_url, 'https://twitter.com/bryanburgers');
	}
}
