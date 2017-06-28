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
	'FORM_FIELD_NUMBER'		=> 'Number',
	'FORM_FIELD_RADIO'		=> 'Radio',
	'FORM_FIELD_RANGE'		=> 'Range',
	'FORM_FIELD_SELECT'		=> 'Select',
	'FORM_FIELD_TELEPHONE'	=> 'Telephone',
	'FORM_FIELD_TEXT'		=> 'Text',
	'FORM_FIELD_TEXTAREA'	=> 'Paragraph',
	'FORM_FIELD_URL'		=> 'URL',

	'CONTENT_DETAIL'			=> 'Content detail',
	'CONTENT_INDEX'				=> 'Content summary',

	'DELETE'					=> 'Delete',

	'EDIT'						=> 'Edit',

	'FIELD_DESCRIPTION'			=> 'Field Description (optional)',
	'FIELD_DETAIL_SHOW'			=> 'Show on content detail page',
	'FIELD_DISPLAY'				=> 'Where should this field be displayed?',
	'FIELD_DISPLAY_LABEL'		=> 'Display field label?',
	'FIELD_INPUT_MODERATOR'		=> 'Moderators only',
	'FIELD_LABEL'				=> 'Field Label',
	'FIELD_LOCATION'			=> 'Where should this field be displayed?',
	'FIELD_NAME'				=> 'Field Name (internal)',
	'FIELD_POSTER'				=> 'Who can input this field?',
	'FIELD_REQUIRED'			=> 'Is this field required?',
	'FIELD_SUMMARY_SHOW'		=> 'Show on content summary',

	'INPUT_SIZE'				=> 'Input size',

	'LABEL_INLINE'				=> 'Yes - Same line',
	'LABEL_NEWLINE'				=> 'Yes - New line',

	'VALIDATE_EXPLAIN'			=> 'Should we validate this field?',

	// checkbox/radio/select settings
	'ADD_OPTION'				=> 'Add Option',
	'ITEMS_PER_COLUMN'			=> 'Items per column',
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

	// number settings
	'NUMBER_MAX_VALUE'			=> 'Maximum value',
	'NUMBER_MIN_VALUE'			=> 'Minimum value',
	'NUMBER_STEP'				=> 'Increase/decrease by',

	// text settings
	'TEXT_MAX_VALUE'			=> 'Maximum value',

	// textarea settings
	'INDEX_MAX_CHARS'			=> 'Maximum characters displayed on summary',
	'TEXTAREA_LARGE'			=> 'Large',
	'TEXTAREA_ENABLE_EDITOR'	=> 'Enable Editor?',
	'TEXTAREA_MAXLENGTH'		=> 'Maximum input characters',
	'TEXTAREA_SIZE'				=> 'Size',
	'TEXTAREA_SMALL'			=> 'Small',
));
