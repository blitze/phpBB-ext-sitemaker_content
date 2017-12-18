<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class share extends base
{
	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  	$language       	Language object
	 * @param \phpbb\request\request_interface				$request			Request object
	 * @param \blitze\sitemaker\services\template			$ptemplate			Sitemaker template object
	 * @param \blitze\sitemaker\services\util				$util       		Sitemaker utility object
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
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field($name, array &$data)
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, $mode = '')
	{
		$props = $data['field_props'];

		$this->util->add_assets(array(
			'js'	=> array(
				'@blitze_content/vendor/jssocials/dist/jssocials.min.js',
				'@blitze_content/assets/fields/display.min.js',
			),
			'css'	=> array(
				'@blitze_content/assets/fields/share/' . $props['theme'] . '.min.css',
			)
		));

		$classes = ['social-share', $props['corners'], $props['placement']];
		$attributes = [
			'class="' . join(' ', array_filter($classes)) . '"',
			'data-show-label="' . $props['show_label'] . '"',
			'data-show-count="' . $props['show_count'] . '"',
			'data-share-in="' . $props['sharein'] . '"',
			'data-shares="' . join(',', $props['defaults']) . '"',
			'style="font-size: ' . $props['size'] . 'px"',
		];

		return '<div ' . join(' ', $attributes) . '></div>';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'corners'		=> '',
			'placement'		=> '',
			'sharein'		=> 'popup',
			'show_label'	=> 'true',
			'show_count'	=> 'false',
			'size'			=> 14,
			'theme'			=> 'flat',
			'options'		=> ['twitter', 'facebook', 'googleplus', 'linkedin', 'pinterest', 'email', 'stumbleupon', 'whatsapp', 'telegram', 'line', 'viber', 'pocket', 'messenger', 'vkontakte'],
			'defaults'		=> ['twitter', 'facebook', 'googleplus', 'linkedin', 'pinterest', 'stumbleupon', 'pocket', 'email'],
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'share';
	}
}
