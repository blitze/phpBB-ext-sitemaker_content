<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class image extends base
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
	public function get_field_value(array $data)
	{
		$value = $this->request->variable($data['field_name'], $data['field_value']);
		$value = $this->get_image_src($value);

		return ($value) ? '[img]' . $value . '[/img]' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$bbcode_value = $this->get_field_value($data);

		$field = $this->get_name();
		$data['field_name'] = $name;
		$data['field_value'] = $this->get_image_src($bbcode_value);

		$this->util->add_assets(array(
			'js'   => array(
				'@blitze_content/assets/form/fields.min.js',
			),
		));

		$this->ptemplate->assign_vars($data);
		$field = $this->ptemplate->render_view('blitze/content', "fields/image.html", $field . '_field');

		$data['field_value'] = $bbcode_value;

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, $mode = '')
	{
		$image = '';
		if ($data['field_value'] || $data['field_props']['default'])
		{
			$image = $this->get_image_html($data['field_value'], $mode, $data['field_props']);
		}
		return $image;
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'default'		=> '',
			'detail_align'	=> '',
			'detail_size'	=> '',
			'summary_align'	=> '',
			'summary_size'	=> '',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'image';
	}

	/**
	 * @param string $bbcode_string
	 * @return string
	 */
	private function get_image_src($bbcode_string)
	{
		return str_replace(array('[img]', '[/img]'), '', $bbcode_string);
	}

	/**
	 * @param string $image
	 * @param string $mode
	 * @param array $field_props
	 * @return string
	 */
	private function get_image_html($image, $mode, array $field_props)
	{
		$image = $image ?: '<img src="' . $field_props['default'] . '" class="postimage" alt="Image" />';

		$html = '<figure class="img-ui">' . $image . '</figure>';
		if ($mode !== 'block')
		{
			$view_props = array_fill_keys(array($mode . '_size', $mode . '_align'), '');
			$image_props = array_filter(array_intersect_key($field_props, $view_props));
			$html = '<div class="' . join(' ', $image_props) . '">' . $html . '</div>';
		}
		return $html;
	}
}
