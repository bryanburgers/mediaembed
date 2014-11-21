<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! class_exists('Mediaembed_Base'))
{
	require PATH_THIRD.'mediaembed/ft_mediaembed_base.php';
}

require_once PATH_THIRD.'mediaembed/config.php';

class Mediaembed_twitter_ft extends Mediaembed_Base {

	var $info = array(
		'name'    => 'Media Embed - Twitter',
		'version' => MEDIAEMBED_VERSION
	);

	function __construct()
	{
		parent::__construct('twitter');
	}
}
