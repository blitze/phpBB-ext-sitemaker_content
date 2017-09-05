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
	public function get_field_value($name, $value)
	{
		$value = $this->request->variable($name, $value);
		$value = $this->get_image_src($value);

		return ($value) ? '[img]' . $value . '[/img]' : '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		$bbcode_value = $this->get_field_value($name, $data['field_value']);

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
		if ($data['field_value'])
		{
			$image = '<figure class="img-ui">' . $data['field_value'] . '</figure>';
			if ($mode !== 'block')
			{
				$image = '<div class="' . join(' ', array_filter($data['field_props'])) . '">' . $image . '</div>';
			}
		}
		return $image;
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'size'	=> '',
			'align'	=> '',
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
}
