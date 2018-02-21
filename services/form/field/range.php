<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class range extends base
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
		return 'range';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'display'	=> 'text',
			'type'		=> 'single',
			'theme'		=> 'skinFlat',
			'size'		=> 100,
			'values'	=> '',
			'prefix'	=> '',
			'postfix'	=> '',
			'min'		=> '',
			'max'		=> '',
			'step'		=> 1,
			'grid'		=> false,
		);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$this->util->add_assets(array(
			'js'   => array(
				100 => '@blitze_content/assets/fields/form.min.js',
			),
		));

		$this->set_assets($data['field_props']['theme']);
		$this->set_range($data);

		return parent::show_form_field($name, $data);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		if (!$data['field_value'])
		{
			return '';
		}

		$callable = 'display_field_' . $data['field_props']['display'];
		return $this->$callable($data);
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function display_field_slider(array $data)
	{
		// do not include assets on preview page as form already handles this
		if (!$this->request->is_set_post('cp'))
		{
			$this->util->add_assets(array(
				'js'   => array(
					100 => '@blitze_content/assets/fields/display.min.js',
				),
			));
		}
		$data['field_props']['disabled'] = true;

		$this->set_assets($data['field_props']['theme']);
		$this->set_range($data);
		$this->ptemplate->assign_vars($data);

		return $this->ptemplate->render_view('blitze/content', 'fields/range.html', 'range_field');
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function display_field_text(array $data)
	{
		$range = $this->get_range($data['field_value']);

		if (sizeof($range))
		{
			array_walk($range, array($this, 'set_prefix'), $data['field_props']['prefix']);
		}

		return join($range, ' - ') . $data['field_props']['postfix'];
	}

	/**
	 * @param string $value
	 * @return array
	 */
	protected function get_range($value)
	{
		return explode(';', preg_replace('/;\s+/', ';', $value));
	}

	/**
	 * @param array $data
	 * @return void
	 */
	protected function set_range(array &$data)
	{
		list($from, $to) = $this->get_range($data['field_value']);

		if ($data['field_props']['values'])
		{
			$values = explode(',', preg_replace('/,\s+/', ',', $data['field_props']['values']));

			$from = array_search($from, $values);
			$to = array_search($to, $values);

			$data['field_props']['from'] = $from;
			$data['field_props']['to'] = $to;
		}
	}

	/**
	 * @param string $item
	 * @param int $key
	 * @param string $prefix
	 * @return void
	 */
	protected function set_prefix(&$item, $key, $prefix)
	{
		$item = $prefix . $item;
	}

	/**
	 * @param string $theme
	 * @return void
	 */
	protected function set_assets($theme)
	{
		$this->util->add_assets(array(
			'js'   => array(
				'@blitze_content/vendor/ion.rangeSlider/js/ion.rangeSlider.min.js',
			),
			'css'   => array(
				'@blitze_content/vendor/ion.rangeSlider/css/ion.rangeSlider.min.css',
				'@blitze_content/vendor/ion.rangeSlider/css/ion.rangeSlider.' . $theme . '.min.css',
			)
		));
	}
}
