<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'mediaembed/config.php';
require_once __DIR__ . '/libraries/autoload.php';

class Mediaembed_upd
{
	var $version = MEDIAEMBED_VERSION;

	function __construct()
	{
		$this->EE =& ee();
	}

	function install()
	{
		$this->EE->load->dbforge();

		$this->EE->db->insert('modules', array(
			'module_name'        => MEDIAEMBED_SHORTNAME,
			'module_version'     => MEDIAEMBED_VERSION,
			'has_cp_backend'     => 'n',
			'has_publish_fields' => 'n'
		));

		return TRUE;
	}

	function update($current = '')
	{
		return TRUE;
	}

	function uninstall()
	{
		// remove row from exp_modules
		$this->EE->db->delete('modules', array('module_name' => MEDIAEMBED_SHORTNAME));

		return TRUE;
	}
}
