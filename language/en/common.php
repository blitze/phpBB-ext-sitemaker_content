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

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'COMMENTS'						=> 'Comments',
	'CONTENT_ANNOUNCEMENTS'			=> 'Recommended %s',
	'CONTENT_GLOBAL_ANNOUNCEMENTS'	=> 'Must Read %s',
	'CONTENT_LAST_READ_TIME'		=> 'Last Read %s',
	'CONTENT_NO_TOPICS'				=> 'Sorry! No topics have been posted for this content type',
	'CONTENT_STICKY_POSTS'			=> 'Featured %s',
	'CONTENT_TOPIC_TIME'			=> 'Recent %s',
	'CONTENT_TOPIC_VIEWS'			=> 'Most Read %s',

	'LOAD_MORE'						=> 'Load more content',

	'POST_NEW'						=> 'Post New',

	'REQ_MOD_INPUT'					=> 'Requires moderator input',

	'SITEMAKER_BROWSING_CONTENT'	=> 'Browsing %s',
	'SITEMAKER_READING_TOPIC'		=> 'Reading topic in %s',

	'READ_MORE'						=> 'Read More',
));
