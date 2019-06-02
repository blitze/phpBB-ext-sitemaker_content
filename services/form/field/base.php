<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

abstract class base implements field_interface
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  $language       Language object
	 * @param \phpbb\request\request_interface			$request		Request object
	 * @param \blitze\sitemaker\services\template		$ptemplate		Sitemaker template object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \blitze\sitemaker\services\template $ptemplate)
	{
		$this->language = $language;
		$this->request = $request;
		$this->ptemplate = $ptemplate;
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array();
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value(array $data)
	{
		return $data['field_value'] ?: '';
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		return $data['field_value'];
	}

	/**
	 * @inheritdoc
	 * @return string
	 */
	public function get_submitted_value(array $data)
	{
		return $this->request->variable($data['field_name'], (string) $data['field_value'], true);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field(array &$data)
	{
		$this->ptemplate->assign_vars($data);

		$field = $this->get_name();
		return $this->ptemplate->render_view('blitze/content', "fields/$field.html", $field . '_field');
	}

	/**
	 * @inheritdoc
	 */
	public function save_field(array $field_data, array $topic_data)
	{
		// we do nothing here as field data is stored in phpbb post
		// for fields that store their own data, this would be used to persist data to a database
	}

	/**
	 * @inheritdoc
	 */
	public function get_langname()
	{
		return strtoupper('FORM_FIELD_' . $this->get_name());
	}

	/**
	 * @inheritdoc
	 */
	public function validate_field(array $data)
	{
		$rules = $this->get_validation_rules($data);

		if (isset($rules['sanitize']))
		{
			$data['field_value'] = filter_var($data['field_value'], $rules['sanitize']);
		}

		$message = '';
		if ($rules['filter'] && filter_var($data['field_value'], $rules['filter'], $rules['options']) === false)
		{
			$message = $this->get_error_message($data);
		}
		return $message;
	}

	/**
	 * @inheritdoc
	 */
	public function get_validation_rules(array $data)
	{
		return array(
			'filter'	=> '',
			'options'	=> array(),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_message(array $data)
	{
		return $this->language->lang('FIELD_INVALID', $data['field_label']);
	}

	/**
	 * @param mixed $value
	 * @return array
	 */
	protected function ensure_is_array($value)
	{
		if (!is_array($value))
		{
			return array_filter(preg_split("/(\n|<br>)/", $value));
		}

		return $value;
	}
}
