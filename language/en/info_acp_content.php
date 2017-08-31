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
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACTION'					=> 'Action',
	'ADD'						=> 'Add',
	'ADD_FIELD'					=> 'Add Field',
	'ADD_ITEM'					=> 'Add New Item',
	'ADD_LABEL_PROMPT'			=> 'Please provide a label for this field',
	'ADD_TYPE'					=> 'Add New Content Type',
	'ALLOW_COMMENTS'			=> 'Allow Comments?',
	'AUTHOR_CONTENTS_EXPLAIN'	=> 'Display other topics from topic author?',

	'CANCEL'					=> 'Cancel',
	'CHANGE_CONTENT_TYPE'		=> 'Change Content type',
	'CHARS'						=> 'Characters',
	'CONFIRM_DELETE'			=> 'Delete Field?',
	'CONFIRM_DELETE_TYPE'		=> 'Are you sure you would like to delete this content type? Keep in mind that this will delete all topics and posts for this content type, unless you transfer it to another content type.',
	'CONTENT_ACTIVE'			=> 'Active',
	'CONTENT_CHANGE_TYPE'		=> 'Change content type',
	'CONTENT_DESC'				=> 'Content Description',
	'CONTENT_DESC_EXPLAIN'		=> 'Any HTML markup entered here will be displayed as is.',
	'CONTENT_DISPLAY_BLOG'		=> 'Blog',
	'CONTENT_DISPLAY_PORTAL'	=> 'Portal',
	'CONTENT_DISPLAY_TILES'		=> 'Tiles',
	'CONTENT_EDITED'			=> 'Your content has been successfully edited',
	'CONTENT_FIELDS'			=> 'Fields',
	'CONTENT_FORUM_EXPLAIN'		=> 'This forum is used by the SiteMaker Content extension. PLEASE DO NOT DELETE.',
	'CONTENT_LANGNAME'			=> 'Display Name',
	'CONTENT_LANGNAME_EXPLAIN'	=> 'The name that will be displayed e.g. Articles, Knowledge Base, etc.<br />Use language constant if name is served from language file.<br /><strong>Tip:</strong> If \'Content Parent\' and \'Display Name\' are the same, only the parent will be displayed on the menu.',
	'CONTENT_NAME'				=> 'Content Type (Internal)',
	'CONTENT_NAME_EXISTS'		=> 'A content type with the name <strong>%s</strong> already exists',
	'CONTENT_NAME_EXPLAIN'		=> 'The internal name of the content type e.g. articles, kb, etc.',
	'CONTENT_PERMISSION'		=> 'Enable permissions?',
	'CONTENT_POST_OPTIONS'		=> '%1$sView your submitted post%2$s<br />%3$sEdit submitted post%4$s<br />%5$sAdd new topic%6$s<br /><br />%7$sReturn to previous page%8$s',
	'CONTENT_RETURN_PREV'		=> '%1$sReturn to previous page%2$s',
	'CONTENT_SETTINGS'			=> 'Settings',
	'CONTENT_STORED'			=> 'Your content has been successfully saved',
	'CONTENT_TYPE'				=> 'Type',
	'CONTENT_TYPES'				=> 'Content Types',
	'CONTENT_TYPE_CREATED'		=> 'Content type successfully created.<br /><br />You may now %1$sset permissions%2$s for this content type',
	'CONTENT_TYPE_DELETE'		=> 'Delete Content Type',
	'CONTENT_TYPE_DELETED'		=> 'Content type deleted successfully',
	'CONTENT_TYPE_NO_EXIST'		=> 'The selected content type does not exist',
	'CONTENT_TYPE_UPDATED'		=> 'Content type updated successfully',
	'CONTENT_VIEW'				=> 'View',
	'CONTENT_VIEW_SETTINGS'		=> 'View Settings',
	'COPY_PERMISSION'			=> 'Copy permissions',
	'COPY_PERMISSION_EXPLAIN'	=> 'Copy permissions from the selected forum and apply it to this content type',

	'DEFAULT'					=> 'Default',
	'DELETE_FIELD'				=> 'Delete field',
	'DELETE_FIELD_CONFIRM'		=> 'Are you sure that you would like to delete this field?',
	'DELETE_SELECTED'			=> 'Delete Selected',
	'DELETE_TOPICS'				=> 'Delete Topics',
	'DELETE_TYPE'				=> 'Delete Content type',
	'DETAIL_TEMPLATE'			=> 'Detail Template',
	'DETAIL_TEMPLATE_EXPLAIN'	=> 'This template will be used to display the content fields when viewing the post body. If a template is not provided, all fields will be displayed',
	'DISPLAY_VIEWS'				=> 'Display views count',
	'DISPLAY_VIEWS_EXPLAIN'		=> 'Display number of times topic has been viewed?',

	'EDIT_FIELD'				=> 'Edit field',
	'EDIT_FORUM_PERMISSIONS'	=> 'Edit ’%s’ Forum Permissions',
	'EDIT_ITEM'					=> 'Edit Item',
	'EDIT_POSTED_CONTENT'		=> '%1$sContinue Editing this post%2$s',
	'EDIT_TYPE'					=> 'Edit Content Type',

	'FIELD'						=> 'Field',
	'FIELDS_TEMPLATE'			=> 'Template (?)',
	'FIELD_TAKEN'				=> 'This content field already exists!',
	'FIELD_TYPE'				=> 'Field Type',
	'FORUM_INITIAL'				=> 'F',

	'GROUP_INITIAL'				=> 'G',

	'INVALID_CONTENT_FIELD'		=> '%1$s is invalid (%2$s)',
	'INVALID_CONTENT_TYPE'		=> 'Oops! Invalid content type',
	'ITEMS_PER_PAGE'			=> 'Number of items per page',

	'LABEL'						=> 'Label',
	'LIVE_PREVIEW'				=> 'Live Preview',

	'MISSING_CONTENT_LANGNAME'	=> 'Missing content language name',
	'MISSING_CONTENT_NAME'		=> 'Missing content name',
	'MISSING_FIELD'				=> 'Missing field name!',
	'MISSING_LABEL'				=> 'Missing field label!',

	'NO_CONTENT_FIELDS'			=> '<strong>No content field was entered.</strong><br />A content field is required for content to be created or displayed.',
	'NO_CONTENT_TYPES'			=> 'No content types have been created',
	'NO_COPY_PERMISSIONS'		=> 'Do not copy permissions',

	'PENDING_TOPICS'			=> 'Pending Topics',
	'PROCEED'					=> 'Proceed',

	'REQ_APPROVAL'				=> 'Requires Approval?',

	'SAMPLE_POST'				=> 'Sample Post',
	'SELECT_CONTENT_TYPE'		=> 'Please select a content type',
	'SHOW_AUTHOR_CONTENTS'		=> 'Show author contents',
	'SHOW_AUTHOR_INFO'			=> 'Show author info',
	'SHOW_DESC_INDEX'			=> 'Show content description on content summary page?',
	'SHOW_DESC_INDEX_EXPLAIN'	=> 'If selected, the content description will be displayed when listing contents for this content type',
	'SHOW_PAGINATION'			=> 'Show pagination',
	'SPECIAL_BBCODE'			=> 'Special BBcode',
	'SPECIAL_BBCODE_EXPLAIN'	=> '<strong>[page]yyy[/page] or [page=xxx]yyy[/page]</strong> - use this bbcode to break your content into pages where <strong>xxx</strong> is the page title and <strong>yyy</strong> is the page content',
	'STATUS'					=> 'Status',
	'SUMMARY_TEMPLATE'			=> 'Summary Template',
	'SUMMARY_TEMPLATE_EXPLAIN'	=> 'This template will be used to display content fields when viewing the topic exerpt. If a template is not provided, all fields will be displayed',

	'TILES_PER_ROW'				=> 'Tiles per row',
	'TILES_PER_ROW_DESKTOP'		=> 'Desktop',
	'TILES_PER_ROW_MOBILE'		=> 'Mobile',
	'TILES_PER_ROW_TABLET'		=> 'Tablet',
	'TITLE'						=> 'Title',
	'TOO_LARGE'					=> 'Too large',
	'TOO_LONG'					=> 'Too long',
	'TOO_SHORT'					=> 'Too short',
	'TOO_SMALL'					=> 'Too small',
	'TOPIC_AUTHOR'				=> 'Author',
	'TOPIC_AUTHOR_AVATAR'		=> 'Author Avatar',
	'TOPIC_AUTHOR_COLOUR'		=> 'Author Colour',
	'TOPIC_AUTHOR_FULL'			=> 'Author + Colour + URL',
	'TOPIC_AUTHOR_URL'			=> 'Author URL',
	'TOPIC_COMMENTS'			=> 'Comments',
	'TOPIC_DATE'				=> 'Topic Date',
	'TOPIC_TITLE'				=> 'Topic Title',
	'TOPIC_URL'					=> 'Topic URL',
	'TOPIC_VIEWS'				=> 'Topic Views',
	'TRANSFER_TOPICS'			=> 'Transfer Topics to',
	'TYPE'						=> 'Type',
	'TYPE_NOT_FOUND'			=> 'The current content type was not found',

	'VIEW_PENDING'				=> 'View Pending',
	'VIEW_SCHEDULED'			=> 'View Scheduled',
	'VIEW_TYPE'					=> 'View content type',

	'WRONG_DATA'				=> 'Wrong data type',
));
