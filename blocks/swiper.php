<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\blocks;

class swiper extends recent
{
	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/** @var string */
	protected $tpl_name = 'swiper';

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \blitze\content\services\types			$content_types		Content types object
	 * @param \blitze\content\services\fields			$fields				Content fields object
	 * @param \blitze\sitemaker\services\date_range		$date_range			Date Range Object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param \blitze\sitemaker\services\util			$util       		Sitemaker utility object
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\language\language $language, \blitze\content\services\types $content_types, \blitze\content\services\fields $fields, \blitze\sitemaker\services\date_range $date_range, \blitze\sitemaker\services\forum\data $forum, \blitze\sitemaker\services\util $util)
	{
		parent::__construct($config, $language, $content_types, $fields, $date_range, $forum);

		$this->util = $util;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_config(array $settings)
	{
		$config = parent::get_config($settings);

		$direction_options = array('horizontal' => 'SWIPER_DIRECTION_HORIZONTAL', 'vertical' => 'SWIPER_DIRECTION_VERTICAL');
		$effect_options = array('slide' => 'SWIPER_EFFECT_SLIDE', 'fade' => 'SWIPER_EFFECT_FADE', 'coverflow' => 'SWIPER_EFFECT_COVERFLOW', 'flip' => 'SWIPER_EFFECT_FLIP');
		$pagination_options = array('' => 'SWIPER_PAGINATION_NONE', 'bullets' => 'SWIPER_PAGINATION_BULLETS', 'fraction' => 'SWIPER_PAGINATION_FRACTION', 'progressbar' => 'SWIPER_PAGINATION_PROGRESSBAR');
		$theme_options = array('default' => 'SWIPER_THEME_DEFAULT', 'white' => 'SWIPER_THEME_WHITE', 'black' => 'SWIPER_THEME_BLACK');
		$text_pos_options = array('top' => 'SWIPER_TEXT_POSITION_TOP', 'left' => 'SWIPER_TEXT_POSITION_LEFT', 'center' => 'SWIPER_TEXT_POSITION_CENTER', 'right' => 'SWIPER_TEXT_POSITION_RIGHT', 'bottom' => 'SWIPER_TEXT_POSITION_BOTTOM', 'after' => 'SWIPER_TEXT_POSITION_AFTER', 'random' => 'SWIPER_TEXT_POSITION_RANDOM');

		return array_merge($config, array(
			'layout'			=> '',
			'legend3'			=> 'SWIPER_SLIDESHOW',
				'navigation'		=> array('lang' => 'SWIPER_NAVIGATION', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => 1),
				'theme'				=> array('lang' => 'SWIPER_THEME', 'validate' => 'string', 'type' => 'select', 'options' => $theme_options, 'default' => ''),
				'pagination'		=> array('lang' => 'SWIPER_PAGINATION', 'validate' => 'string', 'type' => 'select', 'options' => $pagination_options, 'default' => 1),
				'effect'			=> array('lang' => 'SWIPER_EFFECT', 'validate' => 'string', 'type' => 'select', 'options' => $effect_options, 'default' => 'slide'),
				'parallax'			=> array('lang' => 'SWIPER_PARALLAX_IMAGE_URL', 'validate' => 'string', 'type' => 'text', 'default' => ''),
				'direction'			=> array('lang' => 'SWIPER_DIRECTION', 'validate' => 'string', 'type' => 'select:1:0:direction', 'options' => $direction_options, 'default' => 'horizontal', 'append' => '<div id="direction-vertical" class="error small">' . $this->language->lang('SWIPER_HEIGHT_REQUIRED') . '</div>'),
				'height'			=> array('lang' => 'SWIPER_HEIGHT', 'validate' => 'int:0', 'type' => 'number:0', 'default' => 0, 'append' => 'PIXEL'),
				'thumbs'			=> array('lang' => 'SWIPER_THUMBNAILS', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => 0),
				'loop'				=> array('lang' => 'SWIPER_LOOP', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => 1),
				'autoplay'			=> array('lang' => 'SWIPER_AUTOPLAY', 'validate' => 'int:0', 'type' => 'number:0', 'default' => 0, 'append' => 'MILLISECONDS'),
			'legend4'			=> 'SWIPER_SLIDES',
				'text_position'		=> array('lang' => 'SWIPER_TEXT_POSITION', 'validate' => 'string', 'type' => 'select', 'options' => $text_pos_options, 'default' => ''),
				'slides-per-view'	=> array('lang' => 'SWIPER_SLIDES_PER_VIEW', 'validate' => 'int:1', 'type' => 'number:1', 'default' => 1),
				'slides-per-group'	=> array('lang' => 'SWIPER_SLIDES_PER_GROUP', 'validate' => 'int:1', 'type' => 'number:1', 'default' => 1),
				'space-between'		=> array('lang' => 'SWIPER_SPACE_BETWEEN_SLIDES', 'validate' => 'int:0', 'type' => 'number:0', 'default' => 10, 'append' => 'PIXEL'),
				'set-wrapper-size'	=> array('lang' => 'SWIPER_EQUAL_HEIGHT_SLIDES', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => 1),
				'auto-height'		=> array('lang' => 'SWIPER_AUTO_HEIGHT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => 0),
				'centered-slides'	=> array('lang' => 'SWIPER_CENTERED', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => 0),
				'free-mode'			=> array('lang' => 'SWIPER_FREE_MODE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true, 'default' => 0),
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function display(array $bdata, $edit_mode = false)
	{
		$this->util->add_assets(array(
			'js'   => array(
				'@blitze_content/vendor/swiper/dist/js/swiper.min.js',
				'@blitze_content/assets/blocks/swiper.min.js',
			),
			'css'   => array(
				'@blitze_content/assets/blocks/swiper.min.css',
			)
		));

		$this->ptemplate->assign_vars(array(
			'ID'			=> $bdata['bid'],
			'SETTINGS'		=> $bdata['settings'],
		));

		return parent::display($bdata, $edit_mode);
	}
}
