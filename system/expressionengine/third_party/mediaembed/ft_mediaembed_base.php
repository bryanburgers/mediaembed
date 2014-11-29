<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'mediaembed/config.php';
require_once __DIR__ . '/libraries/autoload.php';

use MediaEmbed\Provider\ProviderSet;
use MediaEmbed\Provider\Result;

class Mediaembed_Base extends EE_Fieldtype {
	private $_code;

	function __construct($code)
	{
		parent::__construct();

		$this->_code = $code;

		if (! isset($this->EE->session->cache['mediaembed']))
		{
			$this->EE->session->cache['mediaembed'] = array();
		}
		$this->cache =& $this->EE->session->cache['mediaembed'];

		if (!isset($this->cache['includes'])) {
			$this->cache['includes'] = array();
		}
	}

	function getProviderSet() {
		if (!isset($this->cache['providerset']))
		{
			$providerSet = ProviderSet::load(PATH_THIRD.'mediaembed/providers.xml');
			$this->cache['providerset'] = $providerSet;
		}
		return $this->cache['providerset'];
	}

	function isModuleInstalled() {
		if (!isset($this->cache['module_installed']))
		{
			$results = $this->EE->db->select('*')
				->from('modules')
				->where('module_name', MEDIAEMBED_SHORTNAME)
				->get();

			$installed = $results->num_rows() > 0;
			$this->cache['module_installed'] = $installed;
		}
		return $this->cache['module_installed'];
	}

	function _extract_data($data) {
		if (is_array($data))
		{
			try {
				return Result::parseJson($data['data']);
			}
			catch (Exception $e) {
				return null;
			}
		}

		// In v1.0.0, data was stored as URL|JSONDATA instead of storing it
		// just in the JSONDATA. In that case, massage the data to get it just
		// as JSONDATA.
		if (preg_match("/^https?:\/\/[^|]*\|{/", $data))
		{
			$datas = explode('|', $data, 2);
			$json = json_decode($datas[1]);
			$json->{'mediaembed:original_url'} = $datas[0];
			$data = json_encode($json);
		}

		try {
			return Result::parseJson($data);
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	/**
	 * Allow the Field Type to show up in a Grid.
	 */
	public function accepts_content_type($name)
	{
		switch ($name) {
			case 'channel':
			case 'grid':
			case 'blocks/1':
				return true;

			default:
				return false;
		}
	}

	protected function _include_theme_js($file) {
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;

			$providerSet = $this->getProviderSet();
			$providers = $providerSet->getAll();
			$fieldtypes = array();
			foreach ($providers as $provider)
			{
				$fieldtypes[] = 'mediaembed_'.$provider->code;
			}

			$this->EE->cp->add_to_foot('<script type="text/javascript">var MediaEmbedFieldtypes = ' . json_encode($fieldtypes) . ";</script>");
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->_theme_url().$file.'?version='.$this->info['version'].'"></script>');
		}
	}

	protected function _include_theme_css($file) {
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			$this->EE->cp->add_to_head('<link rel="stylesheet" href="'.$this->_theme_url().$file.'?version='.$this->info['version'].'">');
		}
	}

	/**
	 * Theme URL
	 */
	protected function _theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url.'mediaembed/';
		}

		return $this->cache['theme_url'];
	}


	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function display_field($data)
	{
		if (is_string($data))
		{
			$data = htmlspecialchars_decode($data);
		}
		return $this->_display($data, $this->field_name);
	}

	function grid_display_field($data)
	{
		return $this->_display($data, $this->field_name);
	}

	function _display($data, $name) {
		if (!$this->isModuleInstalled())
		{
			return '<p>The MediaEmbed module must be installed to use this Fieldtype</p>';
		}

		$result = $this->_extract_data($data);

		$oembedUrl = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mediaembed'.AMP.'method=oembed'.AMP.'provider='.$this->_code;

		$data = '';
		$output = '';
		$url = '';

		if (!is_null($result))
		{
			$url = $result->getOriginalUrl();
			$data = htmlspecialchars(json_encode($result->toSerializableObject()));
		}

		if (!is_null($result) && isset($result->html) && $result->html != '')
		{
			$output = '<div class="status success">' . $result->html . '</div>';
		}

		$this->_include_theme_js('js/mediaembed.js');
		$this->_include_theme_css('css/mediaembed.css');
		return <<<EOF
<div class="mediaembed" data-oembed-url="{$oembedUrl}">
	<input type="url" name="{$name}[url]" value="{$url}">
	<input js-data type="hidden" name="{$name}[data]" value="{$data}">
	{$output}
</div>
EOF;
	}

	function save($data)
	{
		$obj = json_decode($data['data']);
		if ($obj) {
			$obj->{'mediaembed:original_url'} = $data['url'];
			return json_encode($obj);
		}
		else {
			return '';
		}
	}

	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$embed = $this->_extract_data($data);
		return is_null($embed) ? '' : $embed->html;
	}

	function replace_tag_catchall($data, $params = array(), $tagdata = FALSE, $modifier)
	{
		$embed = $this->_extract_data($data);
		$obj = null;

		if (!is_null($embed))
		{
			$obj = $embed->toSerializableObject();
		}

		if (!is_null($obj) && $obj->{$modifier})
		{
			return $obj->{$modifier};
		}
		else
		{
			return '';
		}
	}
}
