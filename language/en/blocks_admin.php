<?php
/**
*
* @package phpBB Primetime [English]
* @copyright (c) 2012 Daniel A. (blitze)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
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

	'FIELD_MAX_CHARS'					=> 'Max Characters to display',

	'LIMIT_POST_TIME'					=> 'Limit by post time',

	'MAX_TOPICS'						=> 'Max Topics',

	'NO_CONTENT_TYPE'					=> 'No content types have been created',

	'OFFSET_START'						=> 'Offset start',
	'ORDER_BY'							=> 'Order By',

	'PRIMETIME_CONTENT_BLOCK_ARCHIVE'	=> 'Content Archive',
	'PRIMETIME_CONTENT_BLOCK_CALENDAR'	=> 'Content Calendar',
	'PRIMETIME_CONTENT_BLOCK_RECENT'	=> 'Recent Content',

	'SELECT_FIELDS'						=> 'Select Fields',

	'TOPIC_TIME'						=> 'Topic Time',
	'TOPIC_TYPE'						=> 'Topic Type',
	'TOPIC_VIEWS'						=> 'Topic Views',
));
