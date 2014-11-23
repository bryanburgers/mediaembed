<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'mediaembed/config.php';
require_once __DIR__ . '/libraries/autoload.php';

use MediaEmbed\Provider\ProviderSet;

class Mediaembed_mcp
{
	function __construct()
	{
		$this->EE =& ee();

		//$this->_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mediaembed';
	}

	function oembed() {
		$url = $this->EE->input->get('url');
		$providerCode = $this->EE->input->get('provider');

		if (!$url || !$providerCode)
		{
			return $this->outputJsonError('url and provider are required');
		}

		$providerSet = ProviderSet::load(PATH_THIRD.'mediaembed/providers.xml');

		$provider = $providerSet->getProvider($providerCode);

		if (!$provider)
		{
			return $this->outputJsonError('No provider found');
		}

		try
		{
			$data = $provider->fetch($url);
		}
		catch (Exception $e)
		{
			return $this->outputJsonError($e->getMessage());
		}

		$data->data->{'mediaembed:url'} = $url;
		$data->data->{'mediaembed:provider_code'} = $provider->code;

		return $this->EE->output->send_ajax_response((array)$data->data);
	}

	private function outputJsonError($message)
	{
		return $this->EE->output->send_ajax_response(array(
			'error' => true,
			'message' => $message
			));
	}
}
