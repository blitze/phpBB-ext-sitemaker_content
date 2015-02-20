<?php
/**
*
* content [English]
*
* @package language
* @version $Id: admin.php 824 2010-12-16 20:53:35Z blitze $
* @copyright (c) 2007 phpBB Group 
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
	'ADD_OPTION'				=> 'Add Option',
	'ADD_TYPE'					=> 'Add New Content Type',
	'ALLOW_COMMENTS'			=> 'Allow Comments?',
	'AUTHOR_CONTENTS_EXPLAIN'	=> 'Display other topics from content poster?',

	'CANCEL'					=> 'Cancel',
	'CHANGE_CONTENT_TYPE'		=> 'Change Content type',
	'CHARS'						=> 'Characters',
	'CONFIRM_DELETE'			=> 'Delete Field?',
	'CONFIRM_DELETE_TYPE'		=> 'Are you sure you would like to delete <strong>%s</strong>?<br />Keep in mind that this will delete all topics and posts for this content type, unless you transfer it to another content type.',
	'CONFIRM_SWITCH_TO_TYPE'	=> '<strong>Are you sure you would like to change this post\'s content type?</strong><br />Note that if the new content type has more input fields you may need to provide the missing fields or lose data if the new content type has less fields.',
	'CONTENT_ACTIVE'			=> 'Active',
	'CONTENT_CHANGE_TYPE'		=> 'Change content type',
	'CONTENT_DESC'				=> 'Content Description',
	'CONTENT_DESC_EXPLAIN'		=> 'Any HTML markup entered here will be displayed as is.',
	'CONTENT_DETAIL'			=> 'Content detail',
	'CONTENT_DISPLAY_BLOG'		=> 'Blog',
	'CONTENT_DISPLAY_PORTAL'	=> 'Portal',
	'CONTENT_DISPLAY_TILES'		=> 'Tiles',
	'CONTENT_EDITED'			=> 'Your content has been successfully edited',
	'CONTENT_INDEX'				=> 'Content summary',
	'CONTENT_LANGNAME'			=> 'Display Name',
	'CONTENT_LANGNAME_EXPLAIN'	=> 'The name that will be displayed e.g. Articles, Knowledge Base, etc.<br />Use language constant if name is served from language file.<br /><strong>Tip:</strong> If \'Content Parent\' and \'Display Name\' are the same, only the parent will be displayed on the menu.',
	'CONTENT_NAME'				=> 'Content Type (Internal)',
	'CONTENT_NAME_EXISTS'		=> 'A content type with the name <strong>%s</strong> already exists',
	'CONTENT_NAME_EXPLAIN'		=> 'The internal name of the content type e.g. articles, kb, etc.',
	'CONTENT_PARTS'				=> 'Content Parts',
	'CONTENT_PERMISSION'		=> 'Enable permissions?',
	'CONTENT_POST_OPTIONS'		=> '%1$sView your submitted post%2$s<br />%3$sEdit submitted post%4$s<br />%5$sAdd new topic%6$s<br /><br />%7$sReturn to previous page%8$s',
	'CONTENT_RETURN_PREV'		=> '%1$sReturn to previous page%2$s',
	'CONTENT_SETTINGS'			=> 'Content Settings',
	'CONTENT_STORED'			=> 'Your content has been successfully saved',
	'CONTENT_TYPE'				=> 'Content Type',
	'CONTENT_TYPES'				=> 'Content Types',
	'CONTENT_TYPE_CREATED'		=> 'Content type successfully created.<br /><br />You may now %1$sset permissions%2$s for this content type',
	'CONTENT_TYPE_DELETED'		=> 'Content type deleted successfully',
	'CONTENT_TYPE_NO_EXIST'		=> 'The selected content type does not exist',
	'CONTENT_TYPE_UPDATED'		=> 'Content type updated successfully',
	'CONVERTING_TYPE_EXPLAIN'	=> 'This page requires javascript and popups to be enabled. Please do not close the popup window. It may take from a few seconds to several minutes to completely transfer all posts to the new content type.',
	'COPY_PERMISSION'			=> 'Copy permissions',
	'COPY_PERMISSION_EXPLAIN'	=> 'Copy permissions from the selected forum and apply it to this content type',

	'DEFAULT'					=> 'Default',
	'DELETE'					=> 'Delete',
	'DELETE_FIELD'				=> 'Delete field',
	'DELETE_FIELD_CONFIRM'		=> 'Are you sure that you would like to delete this field?',
	'DELETE_SELECTED'			=> 'Delete Selected',
	'DELETE_TOPICS'				=> 'Delete Topics',
	'DELETE_TYPE'				=> 'Delete Content type',
	'DETAIL_TEMPLATE'			=> 'Detail Template',
	'DETAIL_TEMPLATE_EXPLAIN'	=> 'This template will be used to display the content fields when viewing the post body. If a template is not provided, all fields will be displayed',
	'DISPLAY_TYPE'				=> 'Display Type',
	'DISPLAY_VIEWS'				=> 'Display Views',
	'DISPLAY_VIEWS_EXPLAIN'		=> 'Display number of times topic has been viewed?',
	'DONOT_CHANGE_CONTENT_TYPE'	=> 'Do not change content type',

	'EDIT'						=> 'Edit',
	'EDIT_FIELD'				=> 'Edit field',
	'EDIT_FORUM_PERMISSIONS'	=> 'Edit ’%s’ Forum Permissions',
	'EDIT_ITEM'					=> 'Edit Item',
	'EDIT_POSTED_CONTENT'		=> '%1$sContinue Editing this post%2$s',
	'EDIT_TYPE'					=> 'Edit Content Type',
	'ENABLE_EDITOR'				=> 'Enable Editor?',

	'FIELD'						=> 'Field',
	'FIELDS_TEMPLATE'			=> 'Fields Template (optional)',
	'FIELD_DESCRIPTION'			=> 'Field Description (optional)',
	'FIELD_DETAIL_SHOW'			=> 'Show on content detail page',
	'FIELD_DISPLAY'				=> 'Where should this field be displayed?',
	'FIELD_DISPLAY_LABEL'		=> 'Display field label?',
	'FIELD_INPUT_AUTHOR'		=> 'Topic Author',
	'FIELD_INPUT_MODERATOR'		=> 'Moderators only',
	'FIELD_LABEL'				=> 'Field Display Name',
	'FIELD_LOCATION'			=> 'Where should this field be displayed?',
	'FIELD_NAME'				=> 'Field Name (internal)',
	'FIELD_POSTER'				=> 'Who can input this field?',
	'FIELD_REQUIRED'			=> 'Is this field required?',
	'FIELD_SUMMARY_SHOW'		=> 'Show on content summary',
	'FIELD_TAKEN'				=> 'This content field already exists!',
	'FIELD_TYPE'				=> 'Field Type',
	'FORUM_INITIAL'				=> 'F',

	'GROUP_INITIAL'				=> 'G',

	'INDEX_MAX_CHARS'			=> 'Maximum characters displayed',
	'INVALID_CONTENT_FIELD'		=> '%1$s is invalid (%2$s)',
	'INVALID_CONTENT_TYPE'		=> 'Oops! Invalid content type',
	'ITEMS_PER_PAGE'			=> 'Number of items per page',

	'LABEL'						=> 'Label',
	'LABEL_INLINE'				=> 'Yes - on the same line',
	'LABEL_NEWLINE'				=> 'Yes - on a new line',
	'LARGE'						=> 'Large',
	'LIVE_PREVIEW'				=> 'Live Preview',

	'MISSING_CONTENT_LANGNAME'	=> 'Missing content language name',
	'MISSING_CONTENT_NAME'		=> 'Missing content name',
	'MISSING_FIELD'				=> 'Missing field name!',
	'MISSING_LABEL'				=> 'Missing field label!',
	'MULTI_SELECT'				=> 'Allow multiple selections',

	'NEW_TYPE_NO_EXIST'			=> 'The new Content type to transfer to was not found',
	'NO_COMPATIBLE_TYPES'		=> '<strong>There are no suitable content types for transfer.</strong><br />To be compatible, this content type must have all content fields marked as <strong>required</strong>',
	'NO_CONTENT_FIELDS'			=> '<strong>No content field was entered.</strong><br />A content field is required for content to be created or displayed.',
	'NO_CONTENT_ID'				=> 'Oops! No content id specified',
	'NO_CONTENT_TYPES'			=> 'No content types have been created',
	'NO_COPY_PERMISSIONS'		=> 'Do not copy permissions',

	'PENDING_TOPICS'			=> 'Pending Topics',
	'PLEASE_WAIT'				=> 'Transferring, please wait...',
	'PROCEED'					=> 'Proceed',
	'PT_REQUIRED_FIELDS'		=> '* Required',

	'REQ_APPROVAL'				=> 'Requires Approval?',

	'SAMPLE_POST'				=> 'Sample Post',
	'SELECT_CONTENT_TYPE'		=> 'Please select a content type',
	'SHOW_AUTHOR_CONTENTS'		=> 'Show author contents',
	'SHOW_AUTHOR_INFO'			=> 'Show author info',
	'SHOW_DESC_INDEX'			=> 'Show content description on content summary page?',
	'SHOW_DESC_INDEX_EXPLAIN'	=> 'If selected, the content description will be displayed when listing contents for this content type',
	'SHOW_PAGINATION'			=> 'Show pagination',
	'SIZE'						=> 'Size',
	'SMALL'						=> 'Small',
	'SPECIAL_BBCODE'			=> 'Special BBcode',
	'SPECIAL_BBCODE_EXPLAIN'	=> '<strong>[page]yyy[/page] or [page=xxx]yyy[/page]</strong> - use this bbcode to break your content into pages where <strong>xxx</strong> is the page title and <strong>yyy</strong> is the page content',
	'STATUS'					=> 'Status',
	'SUMMARY_TEMPLATE'			=> 'Summary Template',
	'SUMMARY_TEMPLATE_EXPLAIN'	=> 'This template will be used to display content fields when viewing the topic exerpt. If a template is not provided, all fields will be displayed',

	'TITLE'						=> 'Title',
	'TOO_LARGE'					=> 'Too large',
	'TOO_LONG'					=> 'Too long',
	'TOO_SHORT'					=> 'Too short',
	'TOO_SMALL'					=> 'Too small',
	'TOPICS_CHANGE_FAILED'		=> 'The following topics could not be changed:',
	'TOPICS_CHANGE_SUCCESS'		=> 'The following topics where successfully changed:',
	'TOPICS_PER_GROUP'			=> 'Topics per group',
	'TOPICS_PER_GROUP_EXPLAIN'	=> 'For content views that group topics (e.g. by category or author), this limits the number of topics per group',
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
	'TRANSFERING_TYPE'			=> 'Transferring content type',
	'TRANSFER_SUCCESSFULL'		=> 'The content type "<strong>%1$s</strong>" was successfully transferred to "<strong>%2$s</strong>".',
	'TRANSFER_TOPICS'			=> 'Transfer Topics to',
	'TRANSFER_TOPICS_EXPLAIN'	=> 'This will transfer all topics from this content type to the selected content type.',
	'TYPE'						=> 'Type',
	'TYPE_NOT_COMPATIBLE'		=> 'The selected content type, <strong>%1$s</strong>, is not suitable for transfer from <strong>%2$s</strong>',
	'TYPE_NOT_FOUND'			=> 'The current content type was not found',
	'TYPE_NOT_TRANSFERABLE'		=> 'This content type is <strong>not transferrable</strong> as no other content types exist',
	'TYPE_NO_TOPICS'			=> 'The content type has no transferable topics',

	'VALIDATE_EXPLAIN'			=> 'Should we validate this field?',
	'VIEW_PENDING'				=> 'View Pending',
	'VIEW_POPUP'				=> 'Open Popup',
	'VIEW_SCHEDULED'			=> 'View Scheduled',
	'VIEW_TYPE'					=> 'View content type',

	'WRONG_DATA'				=> 'Wrong data type',
));
