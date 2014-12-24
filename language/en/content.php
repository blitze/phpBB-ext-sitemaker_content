<?php
/**
*
* @package phpBB Primetime [English]
* @copyright (c) 2013 Pico88
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'MORE_IN_CATEGORY'	=> 'More in %s',
	'MORE_FROM_AUTHOR'	=> 'More from %s',
	'CONTENT_EDIT'		=> 'Edit Content',
	'CONTENT_REPORTED'	=> 'This content has been reported',

	'PRON_MALE'			=> 'He',
	'PRON_FEMALE'		=> 'She',
	'PRON_NO_GENDER'	=> 'He/She',

	'AUTHOR_INFO'			=> 'About the Author',
	'AUTHOR_INFO_EXPLAIN'	=> '%1$s has been a member since %2$s. %3$s has posted a total of %4$s %5$s item(s) for a total of %6$s post(s).',
	'AUTHOR_CONTENTS'		=> 'Other %1$s by %2$s',

	'SEARCH_USER_POSTS'		=> 'Search all posts by %s',
	'VIEW_AUTHOR_CONTENTS'	=> 'View all %1$s (items) by %2$s',
	'NEW_COMMENT'			=> 'Leave a Comment',
	'POST_COMMENT'			=> 'Post Comment',
	'EDIT_REASON'			=> 'Edit Reason',

	'NO_CONTENT_TYPES'			=> 'There are no existing content types',
	'NO_CONTENT_ITEM'			=> 'Oops! The requested %s (item) does not exist.',
	'CONTENT_TYPE_NO_EXIST'		=> 'Oops! The requested content type does not exist',
	'CONTENT_NO_EXIST'			=> 'Sorry! The requested topic does not exist',
	'CONTENT_UNAPPROVED'		=> 'This content has not been approved',
	'CONTACT_AUTHOR'			=> 'Contact Author',
));
