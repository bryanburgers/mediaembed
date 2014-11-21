<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'mediaembed/config.php';
require_once __DIR__ . '/libraries/autoload.php';

use MediaEmbed\Provider\ProviderSet;

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

	function _extract_data($data) {
		// Matrix gives us back $data as an array.
		if (is_array($data)) {
			$embed = new stdClass();
			$embed->url = isset($data['url']) ? $data['url'] : '';
			$embed->provider = isset($data['provider']) ? $data['provider'] : '';
			$embed->html = isset($data['html']) ? $data['html'] : '';
			return $embed;
		}

		$datas = explode('|', $data, 3);

		$embed = new stdClass();
		$embed->url = $datas[0];
		$embed->provider = isset($datas[1]) ? $datas[1] : '';
		$embed->html = isset($datas[2]) ? $datas[2] : '';
		return $embed;
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
		return $this->_display($data, $this->field_name);
	}

	function _display($data, $name) {
		$obj = $this->_extract_data($data);

		$oembedUrl = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mediaembed'.AMP.'method=oembed'.AMP.'provider='.$this->_code;

		$html = htmlspecialchars($obj->html);
		$output = '';
		if ($obj->html != '')
		{
			$output = '<div class="status success">' . $obj->html . '</div>';
		}

		$this->_include_theme_js('js/mediaembed.js');
		$this->_include_theme_css('css/mediaembed.css');
		return <<<EOF
<div class="mediaembed" data-oembed-url="{$oembedUrl}">
	<input type="url" name="{$name}[url]" value="{$obj->url}">
	<input data-provider type="hidden" name="{$name}[provider] value="{$obj->provider}">
	<input data-html type="hidden" name="{$name}[html]" value="{$html}">
	{$output}
</div>
EOF;
	}

	/**
	 * Prep data for saving
	 *
	 * @access	public
	 * @param	submitted field data
	 * @return	string to save
	 */
	function save($data)
	{
		$url = $data['url'];
		$provider = $this->_code;
		$html = $data['html'];

		return $url . '|' . $provider . '|' . $html;
	}

	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	field data
	 * @param	field parameters
	 * @param	data between tag pairs
	 * @return	replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$embed = $this->_extract_data($data);
		return $embed->html;
	}
}
