<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class field_controller
{
	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\sitemaker\services\auto_lang */
	protected $auto_lang;

	/** @var \blitze\content\services\form\fields_factory */
	protected $fields_factory;

	/** @var string */
	protected $phpbb_admin_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\event\dispatcher_interface				$phpbb_dispatcher	Event dispatcher object
	 * @param \phpbb\request\request_interface				$request			Request object
	 * @param \phpbb\template\template						$template			Template object
	 * @param \blitze\sitemaker\services\auto_lang			$auto_lang			Auto add lang file
	 * @param \blitze\content\services\form\fields_factory	$fields_factory		Fields factory  object
	 */
	public function __construct(\phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\request\request_interface $request, \phpbb\template\template $template, \blitze\sitemaker\services\auto_lang $auto_lang, \blitze\content\services\form\fields_factory $fields_factory)
	{
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->request = $request;
		$this->template = $template;
		$this->auto_lang = $auto_lang;
		$this->fields_factory = $fields_factory;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle()
	{
		if ($this->request->is_ajax() === false)
		{
			redirect(generate_board_url());
		}

		$this->auto_lang->add('form_fields');

		$this->template->set_custom_style(array(
			array(
				'name' 		=> 'adm',
				'ext_path' 	=> 'adm/style/',
			)),
			array('ext/blitze/content/adm/style')
		);

		$field_data = $this->get_field_data();
		$this->template->assign_var('CONTENT_FIELDS', array(array_change_key_case($field_data, CASE_UPPER)));

		$this->template->set_filenames(array(
			'field'	=> 'content_fields.html'
		));

		return new JsonResponse($this->template->assign_display('field'));
	}

	/**
	 * @return array
	 */
	protected function get_field_data()
	{
		$field_type = $this->request->variable('type', '');
		$fields_data = $this->request->variable('field_data', array('' => array('' => '')), true);
		$field_props = $this->request->variable('field_props', array('' => array('' => '')), true);

		$field_data = (array) array_pop($fields_data);

		// set defaults if adding new field
		$field_data += array(
			'field_detail_ldisp'	=> 1,
			'field_summary_ldisp'	=> 1,
			'field_detail_show'		=> 'body',
			'field_summary_show'	=> 'body',
		);

		/** @var \blitze\content\services\form\field\field_interface $field_instance */
		$field_instance = $this->fields_factory->get($field_type);
		$default_props = $field_instance->get_default_props();

		$field_data['field_type'] = $field_type;
		$field_data['type_label'] = $field_instance->get_langname();
		$field_data['field_props'] = array_replace_recursive($default_props,
			array_intersect_key((array) array_pop($field_props), $default_props)
		);

		$this->set_prop('options', $field_data);
		$this->set_prop('defaults', $field_data);
		$this->force_prop($field_type, $field_data);

		/**
		 * Event to modify field data
		 *
		 * @event blitze.content.field_controller.modify_data
		 * @var	array													field_data		Array containing field data
		 * @var	\blitze\content\services\form\field\field_interface		field_instance	Field instance
		 */
		$vars = array('field_data', 'field_instance');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.field_controller.modify_data', compact($vars)));

		return $field_data;
	}

	/**
	 * @param string $prop	options|defaults
	 * @param array $data
	 * @return void
	 */
	protected function set_prop($prop, array &$data)
	{
		$field_prop = $this->request->variable('field_' . $prop, array('' => array(0 => '')), true);

		if (null !== ($array = array_pop($field_prop)))
		{
			$data['field_props'][$prop] = $array;
		}
	}

	/**
	 * @param string $field_type
	 * @param array $data
	 * @return void
	 */
	protected function force_prop($field_type, array &$data)
	{
		switch ($field_type)
		{
			case 'checkbox':
				$data['field_props']['multi_select'] = true;
			break;
			case 'radio':
				$data['field_props']['multi_select'] = false;
			break;
		}
	}
}
