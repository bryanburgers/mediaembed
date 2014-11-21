<?php

require_once 'system/expressionengine/third_party/mediaembed/libraries/autoload.php';

use \MediaEmbed\Provider\Scheme;

class SchemeTest extends PHPUnit_Framework_TestCase
{
	public function testSchemeInstagram()
	{
		$instagram = new Scheme('https://instagram.com/p/*');
		$this->assertTrue($instagram->test('https://instagram.com/p/1'));
		$this->assertFalse($instagram->test('https://twitter.com/ee_blocks/status/516919035087159296'));
		$this->assertFalse($instagram->test('http://instagram.com/p/1'));
	}

	public function testSchemeTwitter()
	{
		$twitter = new Scheme('https://twitter.com/*');
		$this->assertFalse($twitter->test('https://instagram.com/p/1'));
		$this->assertTrue($twitter->test('https://twitter.com/ee_blocks/status/516919035087159296'));
		$this->assertFalse($twitter->test('http://instagram.com/p/1'));
	}
}
