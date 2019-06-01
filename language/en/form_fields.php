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
	'FORM_FIELD_CHECKBOX'	=> 'Checkbox',
	'FORM_FIELD_COLOR'		=> 'Color',
	'FORM_FIELD_DATETIME'	=> 'Date/Time',
	'FORM_FIELD_HIDDEN'		=> 'Hidden Input',
	'FORM_FIELD_IMAGE'		=> 'Image',
	'FORM_FIELD_LOCATION'	=> 'Location',
	'FORM_FIELD_NUMBER'		=> 'Number',
	'FORM_FIELD_RADIO'		=> 'Radio',
	'FORM_FIELD_RANGE'		=> 'Range',
	'FORM_FIELD_SELECT'		=> 'Select',
	'FORM_FIELD_SHARE'		=> 'Social Share',
	'FORM_FIELD_TELEPHONE'	=> 'Telephone',
	'FORM_FIELD_TEXT'		=> 'Text',
	'FORM_FIELD_TEXTAREA'	=> 'Paragraph',
	'FORM_FIELD_URL'		=> 'URL',

	'CONTENT_DETAIL'			=> 'Content detail',
	'CONTENT_INDEX'				=> 'Content summary',

	'DEFAULT'					=> 'Default',
	'DELETE'					=> 'Delete',

	'EDIT'						=> 'Edit',

	'FIELD_DESCRIPTION'			=> 'Field Description (optional)',
	'FIELD_DISPLAY'				=> 'Where should this field be displayed?',
	'FIELD_DISPLAY_LABEL'		=> 'Display field label?',
	'FIELD_DONOT_SHOW'			=> 'Do not show',
	'FIELD_INPUT_MODERATOR'		=> 'Moderators only',
	'FIELD_LABEL'				=> 'Field Label',
	'FIELD_LOCATION'			=> 'Where should this field be displayed?',
	'FIELD_NAME'				=> 'Field Name (internal)',
	'FIELD_POSTER'				=> 'Who can input this field?',
	'FIELD_REQUIRED'			=> 'Is this field required?',
	'FIELD_SHOW_ABOVE'			=> 'Above title',
	'FIELD_SHOW_BODY'			=> 'Body',
	'FIELD_SHOW_FOOTER'			=> 'Footer',
	'FIELD_SHOW_INLINE'			=> 'Inline',

	'INPUT_SIZE'				=> 'Input size',

	'LABEL_HIDDEN'				=> 'Yes - Hidden (spacer)',
	'LABEL_INLINE'				=> 'Yes - Same line',
	'LABEL_NEWLINE'				=> 'Yes - New line',

	'VALIDATE_EXPLAIN'			=> 'Should we validate this field?',

	// checkbox/radio/select settings
	'ADD_OPTION'				=> 'Add Option',
	'ALIGN_VERTICALLY'			=> 'Align vertically',
	'MULTI_SELECT'				=> 'Allow multiple selections',

	// color settings
	'COLOR_BOX'					=> 'Color box',
	'COLOR_DISPLAY_AS'			=> 'Display as',
	'COLOR_HEX'					=> 'Hex',
	'COLOR_NUM_COLORS'			=> 'Maximum number of colors to input',
	'COLOR_PALETTE'				=> 'Color Palette',
	'COLOR_PALETTE_EXPLAIN'		=> 'Comma-separated list of colours.<br />Use a newline for each row',
	'COLOR_PALETTE_ONLY'		=> 'Show palette only',

	// datetime settings
	'DATETIME_DATE'				=> 'Date',
	'DATETIME_DISPLAY_FORMAT'	=> 'Display formate',
	'DATETIME_FORMAT_EXPLAIN'	=> 'Leave blank to use user’s preference or force display format using <a href="http://www.php.net/date" target="_blank">PHP’s date() syntax</a>',
	'DATETIME_FULL'				=> 'Date + Time',
	'DATETIME_MONTHS'			=> 'Months',
	'DATETIME_TIMEONLY'			=> 'Time only',
	'DATETIME_YEARS'			=> 'Years',
	'DATETIME_TYPE'				=> 'Type',
	'DATETIME_NUM_DATES'		=> 'Number of entries',
	'DATETIME_RANGE'			=> 'Date range?',
	'DATETIME_MAX_DATE'			=> 'Maximum date',
	'DATETIME_MAX_EXPLAIN'		=> 'Maximum possible date to select. It can be a specific date or a statement like ’now’, ’today’, ’tomorrow’, ’+2 days’, ’+3 weeks’, ’next monday’, etc. See <a href="https://www.w3schools.com/php/func_date_strtotime.asp" target="_blank">more</a>',
	'DATETIME_MIN_DATE'			=> 'Minimum date',
	'DATETIME_MIN_EXPLAIN'		=> 'Minimum possible date to select. It can be a specific date or a statement like ’now’, ’today’, ’yesterday’, ’-2 days’, ’-3 weeks’, ’last monday’, etc. See <a href="https://www.w3schools.com/php/func_date_strtotime.asp" target="_blank">more</a>',

	// image settings
	'IMAGE_ALIGN'				=> 'Align',
	'IMAGE_ALIGN_LEFT'			=> 'Left',
	'IMAGE_ALIGN_RIGHT'			=> 'Right',
	'IMAGE_DEFAULT'				=> 'Default Image',
	'IMAGE_SIZE'				=> 'Size',
	'IMAGE_SIZE_SMALL'			=> 'Small',
	'IMAGE_SIZE_MEDIUM'			=> 'Medium',
	'IMAGE_SIZE_LARGE'			=> 'Large',
	'IMAGE_SIZE_FULLWIDTH'		=> 'Full Width',
	'IMAGE_SIZE_CARD'			=> 'Card',

	// location settings
	'LOCATION_DISPLAY_TYPE'				=> 'Display Type',
	'LOCATION_DISPLAY_TYPE_ADDRESS'			=> 'Address',
	'LOCATION_DISPLAY_TYPE_COORDINATES'		=> 'Coordinates',
	'LOCATION_DISPLAY_TYPE_INTERACTIVE_MAP'	=> 'Interactive Map',
	'LOCATION_DISPLAY_TYPE_STATIC_MAP'		=> '*Static Map',
	'LOCATION_MAP_TYPES'				=> 'Allowed Map Types',
	'LOCATION_MAP_TYPES_DEFAULT'			=> 'Default',
	'LOCATION_MAP_TYPES_HYBRID'				=> 'Hybrid',
	'LOCATION_MAP_TYPES_SATELLITE'			=> 'Satellite',
	'LOCATION_MAP_TYPES_TERRAIN'			=> 'Terrain',
	'LOCATION_MAP_ZOOM'					=> 'Map Zoom Level',
	'LOCATION_MAP_ZOOM_USER'				=> 'Author-defined Level',
	'LOCATION_MAP_ZOOM_WORLD'				=> 'World Level',
	'LOCATION_MAP_ZOOM_CONTINENT'			=> 'Continent Level',
	'LOCATION_MAP_ZOOM_CITY'				=> 'City Level',
	'LOCATION_MAP_ZOOM_STREETS'				=> 'Street Level',
	'LOCATION_MAP_ZOOM_BUILDINGS'			=> 'Buildings Level',
	'LOCATION_MAP_WIDTH'				=> 'Map Width',
	'LOCATION_MAP_HEIGHT'				=> 'Map Height',
	'LOCATION_GOOGLE_API_KEY_REQUIRED'	=> '<strong>NB:</strong> This field requires a valid %s<strong>Google Maps JavaScript API key</strong>%s.<br /><strong>*</strong> The Static Map type requires the Google Static Maps API to be enabled in your <a target="_blank" href="https://console.developers.google.com/flows/enableapi?apiid=static_maps_backend&reusekey=true"><strong>Google API Console</strong></a>',

	// number settings
	'NUMBER_MAX_VALUE'			=> 'Maximum value',
	'NUMBER_MIN_VALUE'			=> 'Minimum value',
	'NUMBER_STEP'				=> 'Increase/decrease by',

	// range settings
	'RANGE_DISPLAY'				=> 'Display',
	'RANGE_DISPLAY_TEXT'		=> 'Text',
	'RANGE_DISPLAY_SLIDER'		=> 'Slider',
	'RANGE_TYPE'				=> 'Range Type',
	'RANGE_TYPE_SINGLE'			=> 'Single',
	'RANGE_TYPE_DOUBLE'			=> 'Double',
	'RANGE_SKIN'				=> 'Skin',
	'RANGE_SKIN_BIG'			=> 'Big',
	'RANGE_SKIN_FLAT'			=> 'Flat',
	'RANGE_SKIN_MODERN'			=> 'Modern',
	'RANGE_SKIN_ROUND'			=> 'Round',
	'RANGE_SKIN_SHARP'			=> 'Sharp',
	'RANGE_SKIN_SQUARE'			=> 'Square',
	'RANGE_VALUES'				=> 'Possible range values (optional)',
	'RANGE_VALUES_EXPLAIN'		=> 'Comma-separated (<strong>,</strong>) list of possible slider values (numbers or strings)',
	'RANGE_PREFIX'				=> 'Prefix',
	'RANGE_PREFIX_EXPLAIN'		=> 'Set prefix for values. Will be set up right before the value e.g $10',
	'RANGE_POSTFIX'				=> 'Postfix',
	'RANGE_POSTFIX_EXPLAIN'		=> 'Set postfix for values. Will be set up right after the value e.g 100k',
	'RANGE_MIN_VALUE'			=> 'Minimum number',
	'RANGE_MAX_VALUE'			=> 'Maximum number',
	'RANGE_STEP'				=> 'Step',
	'RANGE_ENABLE_GRID'			=> 'Enable grid?',

	// social share
	'SHARE_CORNERS'				=> 'Corners',
	'SHARE_CORNERS_ROUND'		=> 'Round',
	'SHARE_CORNERS_SQUARE'		=> 'Square',
	'SHARE_PLACEMENT'			=> 'Placement',
	'SHARE_PLACEMENT_CENTER'	=> 'Center',
	'SHARE_PLACEMENT_DEFAULT'	=> 'Default',
	'SHARE_PLACEMENT_LEFT'		=> 'Left',
	'SHARE_PLACEMENT_RIGHT'		=> 'Right',
	'SHARE_SHOW_COUNT'			=> 'Show Count?',
	'SHARE_SHOW_COUNT_OUTSIDE'	=> 'Yes - Outside',
	'SHARE_SHOW_COUNT_INSIDE'	=> 'Yes - Inside',
	'SHARE_SHOW_LABEL'			=> 'Show Label?',
	'SHARE_SITES'				=> 'Select Sites',
	'SHARE_SIZE'				=> 'Size',
	'SHARE_STRATEGY'			=> 'Share Strategy',
	'SHARE_STRATEGY_BLANK'		=> 'Blank Page',
	'SHARE_STRATEGY_POPUP'		=> 'Popup',
	'SHARE_STRATEGY_SELF'		=> 'Same Page',
	'SHARE_THEME'				=> 'Theme',
	'SHARE_THEME_CLASSIC'		=> 'Classic',
	'SHARE_THEME_FLAT'			=> 'Flat',
	'SHARE_THEME_MINI'			=> 'Mini',
	'SHARE_THEME_PLAIN'			=> 'Plain',

	// text settings
	'TEXT_MAX_VALUE'			=> 'Maximum value',

	// textarea settings
	'INDEX_MAX_CHARS'			=> 'Maximum characters displayed on summary',
	'TEXTAREA_LARGE'			=> 'Large',
	'TEXTAREA_ENABLE_EDITOR'	=> 'Enable Editor?',
	'TEXTAREA_MAXLENGTH'		=> 'Maximum input characters',
	'TEXTAREA_SIZE'				=> 'Size',
	'TEXTAREA_SMALL'			=> 'Small',
	'TEXTAREA_TIPS'				=> '<strong>TIP</strong>: You can insert a page break using [pagebreak] or [pagebreak title=Page Title]',
));
