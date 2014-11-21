<?php

require_once 'system/expressionengine/third_party/mediaembed/libraries/autoload.php';

use \MediaEmbed\Provider\Scheme;
use \MediaEmbed\Provider\Provider;
use \MediaEmbed\Provider\ProviderSet;

class ProviderSetTest extends PHPUnit_Framework_TestCase
{
	private function createProviderSet()
	{
		$httpTwitter = new Scheme('http://twitter.com/*');
		$httpsTwitter = new Scheme('https://twitter.com/*');
		$twitterProvider = new Provider('twitter', 'Twitter', array(), array($httpTwitter, $httpsTwitter));

		$httpInstagram1 = new Scheme('http://instagram.com/p/*');
		$httpInstagram2 = new Scheme('http://instagr.am/p/*');
		$httpsInstagram1 = new Scheme('https://instagram.com/p/*');
		$httpsInstagram2 = new Scheme('https://instagr.am/p/*');
		$instagramProvider = new Provider('instagram', 'Instagram', array(), array($httpInstagram1, $httpInstagram2, $httpsInstagram1, $httpsInstagram2));

		return new ProviderSet(array($twitterProvider, $instagramProvider));
	}

	public function testTest()
	{
		$providerSet = $this->createProviderSet();

		$this->assertTrue($providerSet->test('https://twitter.com/ee_blocks/status/516919035087159296'));
		$this->assertTrue($providerSet->test('http://twitter.com/ee_blocks/status/516919035087159296'));
		$this->assertTrue($providerSet->test('https://instagram.com/p/1'));
		$this->assertTrue($providerSet->test('http://instagram.com/p/1'));
		$this->assertFalse($providerSet->test('https://burgers.io/'));
	}

	public function testGetById()
	{
		$providerSet = $this->createProviderSet();

		$provider = $providerSet->getProvider('instagram');

		$this->assertNotNull($provider);
		$this->assertEquals($provider->code, 'instagram');
		$this->assertEquals($provider->name, 'Instagram');

		$this->assertNull($providerSet->getProvider('something'));
	}

	public function testFind()
	{
		$providerSet = $this->createProviderSet();

		$provider = $providerSet->find('https://instagram.com/p/1');

		$this->assertNotNull($provider);
		$this->assertEquals($provider->code, 'instagram');
		$this->assertEquals($provider->name, 'Instagram');

		$this->assertNull($providerSet->find('https://burgers.io/'));
	}

	public function testParse()
	{
		$file = __DIR__ . '/providers.xml';

		$providerSet = ProviderSet::load($file);

		$instagram = $providerSet->getProvider('instagram');
		$twitter = $providerSet->getProvider('twitter');

		$this->assertNotNull($instagram);
		$this->assertNotNull($twitter);

		$this->assertEquals($instagram->code, 'instagram');
		$this->assertEquals($instagram->name, 'Instagram');
		$this->assertEquals(count($instagram->endpoints), 1);
		$this->assertEquals(count($instagram->schemes), 4);
		$this->assertEquals($instagram->endpoints[0]->type, 'application/json+oembed');
		$this->assertEquals($instagram->endpoints[0]->href, 'https://api.instagram.com/oembed');
		$this->assertEquals($instagram->schemes[0]->pattern, 'http://instagram.com/p/*');
	}

	public function testGetAll()
	{
		$file = __DIR__ . '/providers.xml';

		$providerSet = ProviderSet::load($file);

		$providers = $providerSet->getAll();

		$this->assertNotNull($providers);
		$this->assertEquals(count($providers), 2);

		$this->assertEquals($providers[0]->code, 'instagram');
		$this->assertEquals($providers[1]->code, 'twitter');
	}
}
