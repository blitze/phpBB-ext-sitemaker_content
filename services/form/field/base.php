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
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		return $data['field_value'];
	}

	/**
	 * @inheritdoc
	 */
	public function get_field_value(array $data)
	{
		return $this->request->variable($data['field_name'], $data['field_value'], true);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$data['field_name'] = $name;
		$data['field_value'] = $this->get_field_value($data);

		$this->ptemplate->assign_vars($data);

		$field = $this->get_name();
		return $this->ptemplate->render_view('blitze/content', "fields/$field.html", $field . '_field');
	}

	/**
	 * @inheritdoc
	 */
	public function save_field($value, array $field_data, array $topic_data)
	{
		return false;
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
		$options = $this->get_filter_options($data);

		$message = '';
		if (isset($data['validation_filter']) && !filter_var($data['field_value'], $data['validation_filter'], $options))
		{
			$message = $this->get_error_message($data);
		}

		return $message;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	protected function get_filter_options(array &$data)
	{
		if (isset($data['field_minlength']))
		{
			$data['validation_options'] += array('min_range' => $data['field_minlength']);
		}

		if (isset($data['field_maxlength']))
		{
			$data['validation_options'] += array('max_range' => $data['field_maxlength']);
		}

		return (isset($data['validation_options'])) ? array('options' => $data['validation_options']) : false;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function get_error_message(array $data)
	{
		$length = utf8_strlen($data['field_value']);

		if ($this->is_too_short($data, $length))
		{
			return $this->language->lang('FIELD_TOO_SHORT', $data['field_label'], $data['field_minlength']);
		}
		else if ($this->is_too_long($data, $length))
		{
			return $this->language->lang('FIELD_TOO_LONG', $data['field_label'], $data['field_maxlength']);
		}
		else
		{
			return $this->language->lang('FIELD_INVALID', $data['field_label']);
		}
	}

	/**
	 * @param array $data
	 * @param $length
	 * @return bool
	 */
	protected function is_too_short(array $data, $length)
	{
		return (isset($data['field_minlength']) && $length < $data['field_minlength']) ? true : false;
	}

	/**
	 * @param array $data
	 * @param $length
	 * @return bool
	 */
	protected function is_too_long(array $data, $length)
	{
		return (isset($data['field_maxlength']) && $length > $data['field_maxlength']) ? true : false;
	}
}
