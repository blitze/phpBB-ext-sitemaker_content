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

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  $language       Language object
	 * @param \phpbb\request\request_interface			$request		Request object
	 * @param \phpbb\template\template					$template		Template object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \phpbb\template\template $template)
	{
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
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
	public function display_field(array $data, array $topic_data, $display_mode, $view_mode)
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
	public function get_field_template()
	{
		return '@blitze_content/fields/' . $this->get_name() . '.html';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field(array &$data)
	{
		return true;
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
			$data['field_value'] = filter_var($data['field_value'], (int) $rules['sanitize']);
		}

		$message = '';
		if ($rules['filter'] && filter_var($data['field_value'], (int) $rules['filter'], $rules['options']) === false)
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
			return array_filter((array) preg_split("/(\n|<br>)/", $value));
		}

		return $value;
	}
}
