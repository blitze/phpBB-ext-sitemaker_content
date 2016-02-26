<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class form
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\template\context */
	protected $template_context;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $fields = array();

	/** @var array */
	protected $data = array();

	/** @var array */
	protected $form = array();

	/** @var array */
	protected $allowed_bbcodes = array();

	/** @var array */
	protected $custom_tags = array();

	/** @var bool */
	public $enable_editor = false;

	/** @var bool */
	public $is_valid = false;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth					Auth object
	 * @param \phpbb\config\db							$config					Config object
	 * @param \phpbb\template\context					$template_context		Template context object
	 * @param \phpbb\request\request_interface			$request				Request object
	 * @param \phpbb\user								$user					User object
	 * @param \blitze\sitemaker\services\template			$ptemplate				Sitemaker template object
	 * @param string									$phpbb_root_path		Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\request\request_interface $request, \phpbb\di\service_collection $field_drivers, \phpbb\template\context $template_context, \phpbb\user $user, \blitze\sitemaker\services\template $ptemplate, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->request = $request;
		$this->template_context = $template_context;
		$this->user = $user;
		$this->ptemplate = $ptemplate;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->register_fields($field_drivers);

		$this->user->add_lang_ext('blitze/content', 'form');
	}

	protected function register_fields($field_drivers)
	{
		if (!empty($field_drivers))
		{
			foreach ($field_drivers as $service => $driver)
			{
				$this->fields[$driver->get_name()] = $driver;
			}
		}
	}

	public function add($name, $type, $field_data = array(), $item_id = 0)
	{
		$field_data += array('field_id' => 'field-' . $name);
		$field_data += $this->get_default_field_data();

		if (isset($this->fields[$type]))
		{
			$obj = $this->fields[$type];

			$field_data += $obj->get_default_props();

			if ($type === 'textarea')
			{
				if ($field_data['editor'])
				{
					$this->ptemplate->assign_block_vars_array('custom_tags', $this->custom_tags);
					$this->enable_editor = true;
					$field_data += $this->allowed_bbcodes;
				}
			}

			$field_data['field_type'] = $type;
			$field_data['field_view'] = $obj->show_form_field($name, $field_data, $item_id);

			$this->data[$name] = $field_data;
		}

		return $this;
	}

	public function create($form_name, $action, $legend = '', $method = 'post', $forum_id = 0)
	{
		$this->user->add_lang('posting');

		add_form_key($form_name);

		// HTML, BBCode, Smilies, Images and Flash status
		if ($forum_id)
		{
			$img_status		= ($this->auth->acl_get('f_img', $forum_id)) ? true : false;
			$url_status		= ($this->config['allow_post_links']) ? true : false;
			$flash_status	= ($this->auth->acl_get('f_flash', $forum_id) && $this->config['allow_post_flash']) ? true : false;
			$quote_status	= true;

			$this->allowed_bbcodes['S_BBCODE_IMG']		= $img_status;
			$this->allowed_bbcodes['S_BBCODE_FLASH']	= $flash_status;
			$this->allowed_bbcodes['S_BBCODE_QUOTE']	= true;
			$this->allowed_bbcodes['S_BBCODE_URL']		= $url_status;
		}

		// Assigning custom bbcodes
		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		display_custom_bbcodes();

		$rootref = $this->template_context->get_root_ref();
		$dataref = $this->template_context->get_data_ref();

		$this->custom_tags = (isset($dataref['custom_tags'])) ? $dataref['custom_tags'] : array();
		$this->form = array(
			'form_name'		=> $form_name,
			'form_action'	=> $action,
			'form_legend'	=> $legend,
			'form_method'	=> $method,
			'form_key'		=> $rootref['S_FORM_TOKEN'],
		);
		unset($rootref, $dataref);

		return $this;
	}

	public function get_form()
	{
		foreach ($this->data as $field => $row)
		{
			switch ($row['field_type'])
			{
				case 'submit':
				case 'reset':
					$key = 'button';
				break;
				case 'hidden':
					$key = 'hidden';
				break;
				case 'file':
				default:
					$key = 'element';
				break;
			}

			$this->ptemplate->assign_block_vars($key, array_change_key_case($row, CASE_UPPER));
		}

		$this->ptemplate->assign_vars(array(
			'T_ASSETS_PATH'	=> $this->phpbb_root_path . 'assets',
			'S_EDITOR'		=> $this->enable_editor,
		));

		$this->ptemplate->assign_block_vars_array('custom_tags', $this->custom_tags);
		$this->ptemplate->assign_vars(array_change_key_case($this->form, CASE_UPPER));

		return $this->ptemplate->render_view('blitze/content', 'form.html', 'form');
	}

	public function handle_request($request)
	{
		$field_values = array();
		if ($request->server('REQUEST_METHOD') === 'POST')
		{
			$errors = array();
			foreach ($this->data as $field => $row)
			{
				if ($row['field_required'] && $row['field_value'] == '')
				{
					$errors[] = sprintf($this->user->lang['FIELD_REQUIRED'], $row['field_label']);
				}
				else if ($row['field_value'])
				{
					$obj = $this->fields[$row['field_type']];
					$errors[] = $obj->validate_field($row);
				}

				if (!$row['requires_item_id'])
				{
					$field_values[$field] = $row['field_value'];
				}
			}

			if (!check_form_key($this->form['form_name']))
			{
				$errors[] = 'FORM_INVALID';
			}

			$errors = array_filter($errors);

			if (!sizeof($errors))
			{
				$this->is_valid = true;
			}
			else
			{
				$this->is_valid = false;
				$this->ptemplate->assign_var('ERRORS', join('<br />', $errors));
			}
		}

		return $field_values;
	}

	public function save_fields($item_id)
	{
		if (!$item_id)
		{
			return false;
		}

		foreach ($this->data as $field => $row)
		{
			$obj = $this->fields[$row['field_type']];
			$obj->save_field($field, $row['field_value'], $row, $item_id);
		}
	}

	public function get_default_field_data()
	{
		return array(
			'field_label'		=> '',
			'field_explain'		=> '',
			'field_value'		=> '',
			'field_required'	=> false,
		);
	}

	public function get_form_fields()
	{
		return $this->fields;
	}

	public function get_data()
	{
		return $this->data;
	}
}
