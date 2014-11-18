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
	'ACTION'				=> 'Action',
	'VIEW_TYPE'				=> 'View content type',
	'EDIT_TYPE'				=> 'Edit content type',
	'EDIT_ITEM'				=> 'Edit Item',

	'CONTENT_TYPES'			=> 'Content Types',
	'CONTENT_ACTIVE'		=> 'Active',
	'CONTENT_NAME'			=> 'Content Type (Internal)',
	'CONTENT_LANGNAME'		=> 'Display Name',
	'CONTENT_PERMISSION'	=> 'Enable permissions?',
	'CONTENT_DESC'			=> 'Content Description',

	'FIELD'					=> 'Field',
	'LABEL'					=> 'Label',
	'TYPE'					=> 'Type',
	'TOO_SHORT'				=> 'Too short',
	'TOO_LONG'				=> 'Too long',
	'TOO_SMALL'				=> 'Too small',
	'TOO_LARGE'				=> 'Too large',
	'WRONG_DATA'			=> 'Wrong data type',
	'CHARS'					=> 'Characters',

	'FIELD_REQUIRED'		=> 'Is this field required?',
	'FIELD_POSTER'			=> 'Who can input this field?',
	'FIELD_DISPLAY'			=> 'Where should this field be displayed?',
	'FIELD_INPUT_AUTHOR'	=> 'Topic Author',
	'FIELD_INPUT_MODERATOR'	=> 'Moderators only',
	'FIELD_DISPLAY_LABEL'	=> 'Display field label?',
	'FIELD_DESCRIPTION'		=> 'Field Description (optional)',
	'FIELD_LOCATION'		=> 'Where should this field be displayed?',
	'FIELD_SUMMARY_SHOW'	=> 'Show on content summary',
	'FIELD_DETAIL_SHOW'		=> 'Show on content detail page',
	'CONTENT_INDEX'			=> 'Content summary',
	'CONTENT_DETAIL'		=> 'Content detail',
	'SHOW_DESC_INDEX'		=> 'Show content description on content summary page?',
	'COPY_PERMISSION'		=> 'Copy permissions',
	'LIVE_PREVIEW'			=> 'Live Preview',
	'NO_COPY_PERMISSIONS'	=> 'Do not copy permissions',
	'COPY_PERMISSION_EXPLAIN'	=> 'Copy permissions from the selected forum and apply it to this content type',
	'SHOW_DESC_INDEX_EXPLAIN'	=> 'If selected, the content description will be displayed when listing contents for this content type',

	'AUTHOR_CONTENTS_EXPLAIN'	=> 'Display other topics from content poster?',
	'DISPLAY_VIEWS_EXPLAIN'		=> 'Display number of times topic has been viewed?',
	'TOPICS_PER_GROUP_EXPLAIN'	=> 'For content views that group topics (e.g. by category or author), this limits the number of topics per group',
	'VALIDATE_EXPLAIN'			=> 'Should we validate this field?',
	'CONTENT_NAME_EXPLAIN'		=> 'The internal name of the content type e.g. articles, kb, etc.',
	'CONTENT_DESC_EXPLAIN'		=> 'Any HTML markup entered here will be displayed as is.',
	'CONTENT_LANGNAME_EXPLAIN'	=> 'The name that will be displayed e.g. Articles, Knowledge Base, etc.<br />Use language constant if name is served from language file.<br /><strong>Tip:</strong> If \'Content Parent\' and \'Display Name\' are the same, only the parent will be displayed on the menu.',

	'REQ_APPROVAL'		=> 'Requires Approval?',
	'ALLOW_COMMENTS'	=> 'Allow Comments?',
	'DISPLAY_VIEWS'		=> 'Display Views',
	'STATUS'			=> 'Status',
	'FIELD_TYPE'		=> 'Field Type',
	'FIELD_NAME'		=> 'Field Name (internal)',
	'FIELD_LABEL'		=> 'Field Display Name',
	'CONFIRM_DELETE'	=> 'Delete Field?',

	'TITLE'				=> 'Title',
	'DISPLAY_TYPE'		=> 'Display Type',
	'PORTAL'			=> 'Portal',
	'BLOG'				=> 'Blog',

	'CONTENT_TYPE'		=> 'Content Type',
	'EDIT'				=> 'Edit',
	'ADD'				=> 'Add',
	'DELETE'			=> 'Delete',
	'CANCEL'			=> 'Cancel',
	'EDIT_TYPE'			=> 'Edit Content Type',
	'ADD_TYPE'			=> 'Add New Content Type',
	'ADD_ITEM'			=> 'Add New Item',
	'PROCEED'			=> 'Proceed',
	'ADD_FIELD'			=> 'Add Field',
	'ADD_OPTION'		=> 'Add Option',
	'SAMPLE_POST'		=> 'Sample Post',
	'FIELD_TAKEN'		=> 'This content field already exists!',
	'MISSING_FIELD'		=> 'Missing field name!',
	'MISSING_LABEL'		=> 'Missing field label!',

	'TOPIC_AUTHOR'			=> 'Author',
	'TOPIC_AUTHOR_FULL'		=> 'Author + Colour + URL',
	'TOPIC_AUTHOR_URL'		=> 'Author URL',
	'TOPIC_AUTHOR_COLOUR'	=> 'Author Colour',
	'TOPIC_AUTHOR_AVATAR'	=> 'Author Avatar',
	'TOPIC_COMMENTS'		=> 'Comments',
	'TOPIC_DATE'			=> 'Topic Date',
	'TOPIC_TITLE'			=> 'Topic Title',
	'TOPIC_URL'				=> 'Topic URL',
	'TOPIC_VIEWS'			=> 'Topic Views',

	'DEFAULT'				=> 'Default',
	'MULTI_SELECT'			=> 'Allow multiple selections',
	'ADD_LABEL_PROMPT'		=> 'Please provide a label for this field',
	'SPECIAL_BBCODE'		=> 'Special BBcode',
	'SUMMARY_TEMPLATE'		=> 'Summary Template',
	'DETAIL_TEMPLATE'		=> 'Detail Template',
	'EDIT_FIELD'			=> 'Edit field',
	'DELETE_FIELD'			=> 'Delete field',
	'DELETE_FIELD_CONFIRM'	=> 'Are you sure that you would like to delete this field?',

	'CONTENT_PARTS'			=> 'Content Parts',
	'FIELDS_TEMPLATE'		=> 'Fields Template (optional)',
	'CONTENT_SETTINGS'		=> 'Content Settings',
	'CONTENT_TYPE_UPDATED'	=> 'Content type updated successfully',
	'CONTENT_TYPE_DELETED'	=> 'Content type deleted successfully',
	'CONTENT_TYPE_CREATED'	=> 'Content type successfully created.<br /><br />You may now %1$sset permissions%2$s for this content type',
	'CONTENT_EDITED'		=> 'Your content has been successfully edited',
	'CONTENT_STORED'		=> 'Your content has been successfully saved',
	'CONTENT_RETURN_PREV'	=> '%1$sReturn to previous page%2$s',
	'EDIT_POSTED_CONTENT'	=> '%1$sContinue Editing this post%2$s',
	'CONTENT_POST_OPTIONS'	=> '%1$sView your submitted post%2$s<br />%3$sEdit submitted post%4$s<br />%5$sAdd new topic%6$s<br /><br />%7$sReturn to previous page%8$s',
	'CONTENT_CHANGE_TYPE'	=> 'Change content type',
	'TOPICS_CHANGE_SUCCESS'	=> 'The following topics where successfully changed:',
	'TOPICS_CHANGE_FAILED'	=> 'The following topics could not be changed:',
	'TRANSFER_SUCCESSFULL'	=> 'The content type "<strong>%1$s</strong>" was successfully transferred to "<strong>%2$s</strong>".',
	'DELETE_SELECTED'		=> 'Delete Selected',
	'TRANSFER_TOPICS'		=> 'Transfer Topics to',
	'DELETE_TOPICS'			=> 'Delete Topics',
	'PLEASE_WAIT'			=> 'Transferring, please wait...',
	'TRANSFERING_TYPE'		=> 'Transferring content type',
	'CONTENT_TYPE_NO_EXIST'	=> 'The selected content type does not exist',
	'CONTENT_NAME_EXISTS'	=> 'A content type with the name <strong>%s</strong> already exists',
	'VIEW_POPUP'			=> 'Open Popup',
	'VIEW_PENDING'			=> 'View Pending',
	'VIEW_SCHEDULED'		=> 'View Scheduled',
	'PENDING_TOPICS'		=> 'Pending Topics',
	'FORUM_INITIAL'			=> 'F',
	'GROUP_INITIAL'			=> 'G',
	'SHOW_AUTHOR_INFO'		=> 'Show author info',
	'SHOW_AUTHOR_CONTENTS'	=> 'Show author contents',
	'SHOW_PAGINATION'		=> 'Show pagination',
	'ITEMS_PER_PAGE'		=> 'Number of items per page',
	'TOPICS_PER_GROUP'		=> 'Topics per group',
	'LABEL_INLINE'			=> 'Yes - on the same line',
	'LABEL_NEWLINE'			=> 'Yes - on a new line',

	'NEW_TYPE_NO_EXIST'			=> 'The new Content type to transfer to was not found',
	'TYPE_NOT_FOUND'			=> 'The current content type was not found',
	'TYPE_NO_TOPICS'			=> 'The content type has no transferable topics',
	'TYPE_NOT_COMPATIBLE'		=> 'The selected content type, <strong>%1$s</strong>, is not suitable for transfer from <strong>%2$s</strong>',
	'TYPE_NOT_TRANSFERABLE'		=> 'This content type is <strong>not transferrable</strong> as no other content types exist',
	'MISSING_CONTENT_LANGNAME'	=> 'Missing content language name',
	'MISSING_CONTENT_NAME'		=> 'Missing content name',
	'INVALID_CONTENT_FIELD'		=> '%1$s is invalid (%2$s)',
	'SELECT_CONTENT_TYPE'		=> 'Please select a content type',
	'CHANGE_CONTENT_TYPE'		=> 'Change Content type',
	'DELETE_TYPE'				=> 'Delete Content type',
	'DONOT_CHANGE_CONTENT_TYPE'	=> 'Do not change content type',
	'EDIT_FORUM_PERMISSIONS'	=> 'Edit ’%s’ Forum Permissions',
	'CONFIRM_SWITCH_TO_TYPE'	=> '<strong>Are you sure you would like to change this post\'s content type?</strong><br />Note that if the new content type has more input fields you may need to provide the missing fields or lose data if the new content type has less fields.',
	'CONFIRM_DELETE_TYPE'		=> 'Are you sure you would like to delete <strong>%s</strong>?<br />Keep in mind that this will delete all topics and posts for this content type, unless you transfer it to another content type.',
	'CONVERTING_TYPE_EXPLAIN'	=> 'This page requires javascript and popups to be enabled. Please do not close the popup window. It may take from a few seconds to several minutes to completely transfer all posts to the new content type.',
	'TRANSFER_TOPICS_EXPLAIN'	=> 'This will transfer all topics from this content type to the selected content type.', 
	'SPECIAL_BBCODE_EXPLAIN'	=> '<strong>[page]yyy[/page] or [page=xxx]yyy[/page]</strong> - use this bbcode to break your content into pages where <strong>xxx</strong> is the page title and <strong>yyy</strong> is the page content',
	'INVALID_CONTENT_TYPE'		=> 'Oops! Invalid content type',
	'NO_COMPATIBLE_TYPES'		=> '<strong>There are no suitable content types for transfer.</strong><br />To be compatible, this content type must have all content fields marked as <strong>required</strong>',
	'NO_CONTENT_ID'				=> 'Oops! No content id specified',
	'NO_CONTENT_FIELDS'			=> '<strong>No content field was entered.</strong><br />A content field is required for content to be created or displayed.',
	'SUMMARY_TEMPLATE_EXPLAIN'	=> 'This template will be used to display content fields when viewing the topic exerpt. If a template is not provided, all fields will be displayed',
	'DETAIL_TEMPLATE_EXPLAIN'	=> 'This template will be used to display the content fields when viewing the post body. If a template is not provided, all fields will be displayed',
));

?>