<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\controller;

class field_controller
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\form\fields_factory */
	protected $fields_factory;

	/** @var string */
	protected $phpbb_admin_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface				$request			Request object
	 * @param \phpbb\template\template						$template			Template object
	 * @param \blitze\content\services\form\fields_factory	$fields_factory		Fields factory  object
	 * @param string										$admin_path			Admin path
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\template\template $template, \blitze\content\services\form\fields_factory $fields_factory, $admin_path)
	{
		$this->request = $request;
		$this->template = $template;
		$this->fields_factory = $fields_factory;
		$this->phpbb_admin_path = $admin_path;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle()
	{
		global $phpbb_root_path;

		// Set custom style for admin area
		$module_style_dir = 'ext/blitze/content/adm/style';
		$this->template->set_style(array($module_style_dir, 'styles'));

		$data = array(
			'field_name'	=> $this->request->variable('field_name', ''),
			'field_label'	=> $this->request->variable('field_label', '', true),
			'field_type'	=> $this->request->variable('field_type', ''),
			'type_label'	=> $this->request->variable('type_label', '', true),
			'field_type'	=> $this->request->variable('field_type', ''),
		);

		/** @var /blitze/content/services/form/field/field_interface $object */
		$field = $this->fields_factory->get($data['field_type']);

		$data['field_props'] = $field->get_default_props();

		$this->template->assign_block_vars('field', array_change_key_case($data, CASE_UPPER));

		$this->template->set_filenames(array(
			'field'	=> 'content_fields.html'
		));

		return $this->template->assign_display('field');
	}
}
