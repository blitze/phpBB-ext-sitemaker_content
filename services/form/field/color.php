<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class color extends base
{
	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  $language       Language object
	 * @param \phpbb\request\request_interface			$request		Request object
	 * @param \blitze\sitemaker\services\template		$ptemplate		Sitemaker template object
	 * @param \blitze\sitemaker\services\util			$util       	Sitemaker utility object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \blitze\sitemaker\services\template $ptemplate, \blitze\sitemaker\services\util $util)
	{
		parent::__construct($language, $request, $ptemplate);

		$this->util = $util;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'color';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'display'		=> 'box',
			'num_colors'	=> 1,
			'palette'		=> '',
			'palette_only'	=> false,
		);
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function get_field_value(array $data)
	{
		return $this->ensure_is_array($data['field_value']);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		$sep = $this->language->lang('COMMA_SEPARATOR');
		$field_value = $data['field_value'];

		if ($data['field_props']['display'] === 'box')
		{
			$sep = ' ';
			$field_value = array_map(array($this, 'make_box'), $field_value);
		}

		return join($sep, $field_value);
	}

	/**
	 * @inheritdoc
	 */
	public function get_submitted_value(array $data, $form_is_submitted = false)
	{
		if ($form_is_submitted)
		{
			return $this->request->variable($data['field_name'], array(0 => ''));
		}

		return $this->get_field_value($data);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field(array &$data)
	{
		$this->util->add_assets(array(
			'js'   => array(
				'@blitze_content/vendor/spectrum/spectrum.min.js',
				100 => '@blitze_content/assets/fields/form.min.js',
			),
			'css'   => array(
				'@blitze_content/vendor/spectrum/spectrum.min.css',
			),
		));

		return parent::show_form_field($data);
	}

	/**
	 * @param string $color
	 * @return string
	 */
	protected function make_box($color)
	{
		$style = 'display: inline-block; width: 15px; height: 15px; border: 1 solid #fff; border-radius: 4px; background-color: ' . $color;
		return ($color) ? '<div style="' . $style . '" title="' . $color . '"></div>' : '';
	}
}
