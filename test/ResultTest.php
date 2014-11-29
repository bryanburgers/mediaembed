<?php

require_once 'system/expressionengine/third_party/mediaembed/libraries/autoload.php';

use \MediaEmbed\Provider\Result;

class ResultTest extends PHPUnit_Framework_TestCase
{
	public function testResult()
	{
		$obj = new stdClass();
		$obj->html = '<p>Test</p>';
		$obj->author_url = 'https://burgers.io/';
		$obj->author_name = 'Bryan Burgers';

		$randomAttribute = 'rand' . rand(1000, 9999);
		$obj->{$randomAttribute} = 'random';

		$url = 'https://burgers.io/test';
		$providerCode = 'burgersio';

		$result = new Result($url, $providerCode, $obj);

		$this->assertObjectHasAttribute('html', $result);
		$this->assertObjectHasAttribute('author_url', $result);
		$this->assertObjectHasAttribute('author_name', $result);
		$this->assertObjectHasAttribute($randomAttribute, $result);

		$this->assertEquals($result->html, '<p>Test</p>');
		$this->assertEquals($result->author_url, 'https://burgers.io/');
		$this->assertEquals($result->author_name, 'Bryan Burgers');
		$this->assertEquals($result->{$randomAttribute}, 'random');

		$this->assertEquals($result->getOriginalUrl(), $url);
		$this->assertEquals($result->getProviderCode(), $providerCode);
	}

	public function testToSerializableObject()
	{
		$obj = new stdClass();
		$obj->html = '<p>Test</p>';
		$obj->author_url = 'https://burgers.io/';
		$obj->author_name = 'Bryan Burgers';

		$randomAttribute = 'rand' . rand(1000, 9999);
		$obj->{$randomAttribute} = 'random';

		$url = 'https://burgers.io/test';
		$providerCode = 'burgersio';

		$result = new Result($url, $providerCode, $obj);

		$serializableObject = $result->toSerializableObject();

		$this->assertObjectHasAttribute('html', $serializableObject);
		$this->assertObjectHasAttribute('author_url', $serializableObject);
		$this->assertObjectHasAttribute('author_name', $serializableObject);
		$this->assertObjectHasAttribute('mediaembed:original_url', $serializableObject);
		$this->assertObjectHasAttribute('mediaembed:provider_code', $serializableObject);
		$this->assertObjectHasAttribute($randomAttribute, $serializableObject);
		$this->assertObjectNotHasAttribute('_originalUrl', $serializableObject);
		$this->assertObjectNotHasAttribute('_providerCode', $serializableObject);

		$this->assertEquals($serializableObject->html, '<p>Test</p>');
		$this->assertEquals($serializableObject->author_url, 'https://burgers.io/');
		$this->assertEquals($serializableObject->author_name, 'Bryan Burgers');
		$this->assertEquals($serializableObject->{'mediaembed:original_url'}, $url);
		$this->assertEquals($serializableObject->{'mediaembed:provider_code'}, $providerCode);
		$this->assertEquals($serializableObject->{$randomAttribute}, 'random');
	}

	public function testParse()
	{
		$json = '{"html":"<p>Test</p>","author_url":"https://burgers.io/","author_name":"Bryan Burgers","mediaembed:original_url":"https://burgers.io/test","mediaembed:provider_code":"burgersio"}';

		$result = Result::parseJSON($json);

		$this->assertObjectHasAttribute('html', $result);
		$this->assertObjectHasAttribute('author_url', $result);
		$this->assertObjectHasAttribute('author_name', $result);

		$this->assertEquals($result->html, '<p>Test</p>');
		$this->assertEquals($result->author_url, 'https://burgers.io/');
		$this->assertEquals($result->author_name, 'Bryan Burgers');

		$this->assertEquals($result->getOriginalUrl(), "https://burgers.io/test");
		$this->assertEquals($result->getProviderCode(), "burgersio");
	}

	public function testFailedParseEmpty()
	{
		$result = Result::parseJSON('');

		$this->assertNull($result);
	}

	public function testFailedParse()
	{
		$result = Result::parseJSON('not json');

		$this->assertNull($result);
	}
}
