<?php
/**
 *
 * @package phpBB Sitemaker [English]
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'CONTENT_TYPE'						=> 'Content Type',
	'CONTENT_TYPE_ANY'					=> 'Any',

	'DISPLAY_LAYOUT'					=> 'Layout',

	'FIELD_MAX_CHARS'					=> 'Max Characters to display',

	'LIMIT_POST_TIME'					=> 'Limit by post time',

	'MAX_TOPICS'						=> 'Max Topics',
	'MILLISECONDS'						=> 'ms',
	'MONTH_FORMAT'						=> 'Month display format',
	'MONTH_FORMAT_LONG'					=> 'Long',
	'MONTH_FORMAT_SHORT'				=> 'Short',

	'NO_CONTENT_TYPE'					=> 'Please select a content type',

	'OFFSET_START'						=> 'Offset start',
	'ORDER_BY'							=> 'Order By',

	'BLITZE_CONTENT_BLOCK_ARCHIVE'		=> 'Content Archive',
	'BLITZE_CONTENT_BLOCK_CALENDAR'		=> 'Content Calendar',
	'BLITZE_CONTENT_BLOCK_RECENT'		=> 'Recent Content',
	'BLITZE_CONTENT_BLOCK_SWIPER'		=> 'Content Slideshow',

	'SELECT_FIELDS'						=> 'Select Fields',
	'SELECT_FIELDS_EXPLAIN'				=> 'Only display the selected fields',
	'SHOW_ALL_MONTHS'					=> 'Show all months?',
	'SHOW_TOPICS_COUNT'					=> 'Show topics count?',
	'SWIPER_AUTO_HEIGHT'				=> 'Auto height',
	'SWIPER_AUTOPLAY'					=> 'Autoplay',
	'SWIPER_CENTERED'					=> 'Centered slides?',
	'SWIPER_DIRECTION'					=> 'Slide Direction',
	'SWIPER_DIRECTION_HORIZONTAL'		=> 'Horizontal',
	'SWIPER_DIRECTION_VERTICAL'			=> 'Vertical',
	'SWIPER_EFFECT'						=> 'Slide Effect',
	'SWIPER_EFFECT_COVERFLOW'			=> 'Coverflow',
	'SWIPER_EFFECT_FADE'				=> 'Fade',
	'SWIPER_EFFECT_FLIP'				=> 'Flip',
	'SWIPER_EFFECT_SLIDE'				=> 'Slide',
	'SWIPER_EQUAL_HEIGHT_SLIDES'		=> 'Equal heights?',
	'SWIPER_FREE_MODE'					=> 'Free mode?',
	'SWIPER_FREE_MODE_EXPLAIN'			=> 'Slides will not have fixed positions',
	'SWIPER_HEIGHT'						=> 'Height',
	'SWIPER_HEIGHT_REQUIRED'			=> 'Height must be provided below',
	'SWIPER_LOOP'						=> 'Infinite loop?',
	'SWIPER_NAVIGATION'					=> 'Navigation?',
	'SWIPER_THEME'						=> 'Theme',
	'SWIPER_THEME_BLACK'				=> 'Black',
	'SWIPER_THEME_DEFAULT'				=> 'Default',
	'SWIPER_THEME_WHITE'				=> 'White',
	'SWIPER_PAGINATION'					=> 'Pagination',
	'SWIPER_PAGINATION_BULLETS'			=> 'Bullets',
	'SWIPER_PAGINATION_FRACTION'		=> 'Fraction',
	'SWIPER_PAGINATION_NONE'			=> 'None',
	'SWIPER_PAGINATION_PROGRESSBAR'		=> 'Progressbar',
	'SWIPER_PARALLAX_IMAGE_URL'			=> 'Parallax image',
	'SWIPER_SLIDES'						=> 'Slides',
	'SWIPER_SLIDES_PER_GROUP'			=> 'Slides per group',
	'SWIPER_SLIDES_PER_VIEW'			=> 'Slides per view',
	'SWIPER_SLIDESHOW'					=> 'Slideshow',
	'SWIPER_SPACE_BETWEEN_SLIDES'		=> 'Space between slides',
	'SWIPER_TEXT_POSITION'				=> 'Text Position',
	'SWIPER_TEXT_POSITION_AFTER'		=> 'After',
	'SWIPER_TEXT_POSITION_BOTTOM'		=> 'Bottom',
	'SWIPER_TEXT_POSITION_CENTER'		=> 'Center',
	'SWIPER_TEXT_POSITION_LEFT'			=> 'Left',
	'SWIPER_TEXT_POSITION_RANDOM'		=> 'Random',
	'SWIPER_TEXT_POSITION_RIGHT'		=> 'Right',
	'SWIPER_TEXT_POSITION_TOP'			=> 'Top',
	'SWIPER_THUMBNAILS'					=> 'Thumbnails?',

	'TOPIC_TIME'						=> 'Topic Time',
	'TOPIC_TYPE'						=> 'Topic Type',
	'TOPIC_VIEWS'						=> 'Topic Views',

	// Overwrite phpBB post types
	'POST_STICKY'			=> 'Featured',
	'POST_GLOBAL'			=> 'Must Read',
	'POST_ANNOUNCEMENT'		=> 'Recommended',
));
