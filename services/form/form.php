<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form;

class form
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\context */
	protected $template_context;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \blitze\sitemaker\services\auto_lang */
	protected $auto_lang;

	/** @var \blitze\content\services\form\fields_factory */
	protected $fields_factory;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/** @var array */
	protected $db_fields = array();

	/** @var array */
	protected $data = array();

	/** @var array */
	protected $form = array();

	/** @var array */
	protected $errors = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface					$request				Request object
	 * @param \phpbb\template\context							$template_context		Template context object
	 * @param \phpbb\language\language							$language				Language object
	 * @param \blitze\sitemaker\services\auto_lang				$auto_lang				Auto add lang file
	 * @param \blitze\content\services\form\fields_factory		$fields_factory			Form fields factory
	 * @param \blitze\sitemaker\services\template				$ptemplate				Sitemaker template object
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\template\context $template_context, \phpbb\language\language $language, \blitze\sitemaker\services\auto_lang $auto_lang, \blitze\content\services\form\fields_factory $fields_factory, \blitze\sitemaker\services\template $ptemplate)
	{
		$this->request = $request;
		$this->template_context = $template_context;
		$this->language = $language;
		$this->auto_lang = $auto_lang;
		$this->fields_factory = $fields_factory;
		$this->ptemplate = $ptemplate;
	}

	/**
	 * @param string $form_name
	 * @param string $form_key
	 * @param string $action
	 * @param string $legend
	 * @param string $method
	 * @return \blitze\content\services\form\form
	 */
	public function create($form_name, $form_key, $action = '', $legend = '', $method = 'post')
	{
		$this->auto_lang->add('form_fields');
		$this->language->add_lang('posting');

		add_form_key($form_key);

		$this->form = array(
			'form_name'		=> $form_name,
			'form_action'	=> $action,
			'form_legend'	=> $legend,
			'form_method'	=> $method,
			'form_key'		=> $this->template_context->get_root_ref()['S_FORM_TOKEN'],
			'is_submitted'	=> $this->request->is_set_post('form_token'),
		);

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is_created()
	{
		return (bool) count($this->form);
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param array $field_data
	 * @param int $forum_id
	 * @param int $topic_id
	 * @return \blitze\content\services\form\form
	 */
	public function add($name, $type, array $field_data, $forum_id = 0, $topic_id = 0)
	{
		$field_data += array('field_id' => 'field-' . $name);
		$field_data += $this->get_default_field_data();

		if (($field = $this->fields_factory->get($type)) !== null)
		{
			$field_data['field_name'] = $name;
			$field_data['field_type'] = $type;
			$field_data['field_props'] += $field->get_default_props();
			$field_data['field_label'] = $this->language->lang($field_data['field_label']);
			$field_data['field_value'] = $field->get_submitted_value($field_data, $this->form['is_submitted']);
			$field_data['field_view'] = $field->show_form_field($field_data);

			if ($field_data['field_view'])
			{
				$this->data[$name] = $field_data;
			}
		}

		return $this;
	}

	/**
	 * @param bool $wrap_form_element
	 * @return string
	 */
	public function get_form($wrap_form_element = true)
	{
		foreach ($this->data as $row)
		{
			$key = $this->get_field_key($row['field_type']);
			$this->ptemplate->assign_block_vars($key, array_change_key_case($row, CASE_UPPER));
		}

		$this->ptemplate->assign_vars(array_merge(
			array(
				'S_EDITOR'		=> true,
				'S_WRAP_FORM'	=> $wrap_form_element
			),
			array_change_key_case($this->form, CASE_UPPER))
		);

		return $this->ptemplate->render_view('blitze/content', 'form.html', 'form');
	}

	/**
	 * @return array
	 */
	public function handle_request()
	{
		$field_data = array();
		if ($this->request->server('REQUEST_METHOD') === 'POST')
		{
			if (!check_form_key($this->form['form_key']))
			{
				$this->errors[] = 'FORM_INVALID';
			}

			$req_mod_input = false;
			$field_data = $this->get_submitted_data($this->data, $req_mod_input);
		}

		return $field_data;
	}

	/**
	 * @return array
	 */
	public function get_data()
	{
		return $this->data;
	}

	/**
	 * @return array
	 */
	public function get_errors()
	{
		// Replace "error" strings with their real, localised form
		return array_map(array($this->language, 'lang'), array_filter($this->errors));
	}

	/**
	 * @param array $content_fields
	 * @param bool $req_mod_input
	 * @param string $cp_class ucp|mcp
	 * @return array
	 */
	public function get_submitted_data(array $content_fields, &$req_mod_input, $cp_class = 'ucp')
	{
		$previewing = $this->request->is_set('preview');

		$fields_data = array();
		foreach ($content_fields as $field => $row)
		{
			$row += $this->get_default_field_data();
			$value = $this->get_submitted_field_data($row, $req_mod_input, $cp_class);

			if ($previewing || empty($row['field_props']['is_db_field']))
			{
				$fields_data[$field] = $value;
			}
			else
			{
				$this->db_fields[$field] = $value;
			}
		}

		return array_filter($fields_data);
	}

	/**
	 * @param array $topic_data
	 * @param array $content_fields
	 * @return void
	 */
	public function save_db_fields(array $topic_data, array $content_fields)
	{
		foreach ($this->db_fields as $field_type => $value)
		{
			$field = $this->fields_factory->get($field_type);

			$field_data = $content_fields[$field_type];
			$field_data['field_value'] = $value;
			$field_data['field_props'] += $field->get_default_props();

			$field->save_field($field_data, $topic_data);
		}
	}

	/**
	 * @param array $row
	 * @param bool $req_mod_input
	 * @param string $cp_class
	 * @return mixed
	 */
	protected function get_submitted_field_data(array &$row, &$req_mod_input, $cp_class)
	{
		if ($field = $this->fields_factory->get($row['field_type']))
		{
			$row['field_props'] += $field->get_default_props();
			$row['field_value'] = $field->get_submitted_value($row);

			$this->validate_field($field, $row, $req_mod_input, $cp_class);
		}

		return $row['field_value'];
	}

	/**
	 * @param \blitze\content\services\form\field\field_interface $field
	 * @param array $row
	 * @param bool $req_mod_input
	 * @param string $cp_class
	 * @return void
	 */
	protected function validate_field(\blitze\content\services\form\field\field_interface $field, $row, &$req_mod_input, $cp_class)
	{
		if (!empty($row['field_value']))
		{
			$this->errors[] = $field->validate_field($row);
		}
		else if ($row['field_required'])
		{
			if (!$row['field_mod_only'] || $cp_class === 'mcp')
			{
				$this->errors[] = $this->language->lang_array('CONTENT_FIELD_REQUIRED', array($row['field_label']));
			}
			else
			{
				$req_mod_input = true;
			}
		}
	}

	/**
	 * @return array
	 */
	protected function get_default_field_data()
	{
		return array(
			'field_label'		=> '',
			'field_explain'		=> '',
			'field_value'		=> '',
			'field_required'	=> false,
			'field_props'		=> array(),
		);
	}

	/**
	 * @param string $field_type
	 * @return string
	 */
	protected function get_field_key($field_type)
	{
		return ($field_type === 'hidden') ? 'hidden' : 'element';
	}
}
