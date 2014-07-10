<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\acp;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
*
*/
class content_module
{
	var $u_action;
	var $tpl_path;

	function main($id, $mode)
	{
		global $config, $db, $user, $cache, $auth, $template, $phpbb_root_path, $phpbb_admin_path, $phpbb_container, $phpEx;

		include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);

		$user->add_lang('acp/modules');

		$action = request_var('action', 'ACP_CONTENT');
		$field_id = request_var('f', 0);
		$content_id = request_var('t', 0);
		$submit = (isset($_POST['submit'])) ? true : false;

		$optionables = array('checkbox', 'radio', 'select');
		$content = $phpbb_container->get('primetime.content.types');
		$primetime = $phpbb_container->get('primetime');
		$form = $phpbb_container->get('primetime.form.builder');
		$asset_path = $primetime->asset_path;

		if ($submit)
		{
			switch ($action)
			{
				case 'add_type':
				case 'edit_type':

					$action = $message = '';
					$set_permissions = request_var('permission', 0);
					$fields_data = utf8_normalize_nfc(request_var('fdata', array('' => array('' => ''))));
					$content_name = str_replace(' ', '_', strtolower(trim(request_var('content_name', ''))));
					$content_langname = utf8_normalize_nfc(request_var('content_langname', '', true));
					$content_status = request_var('active', 1);
					$display_type = request_var('display_type', 0);

					//Let's do some checks
					if (!$content_name)
					{
						trigger_error($user->lang['MISSING_CONTENT_NAME'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (!$content_langname)
					{
						trigger_error($user->lang['MISSING_CONTENT_LANGNAME'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$content_data = $form_fields = array();
					$content_fields = $form->get_form_fields();

					if (!sizeof($fields_data))
					{
						trigger_error($user->lang['NO_CONTENT_FIELDS'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if ($content_id)
					{
						$sql = 'SELECT * 
							FROM ' . CONTENT_TYPES_TABLE . '
							WHERE content_id = ' . (int) $content_id;
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$forum_id = $row['forum_id'];

						if ($content_name !== $row['content_name'])
						{
							if ($this->content_type_exists($content_name))
							{
								$back_url = $this->u_action . '&amp;action=edit_type&amp;t=' . $content_id;
								trigger_error($user->lang['CONTENT_MODE_EXISTS'] . adm_back_link($back_url), E_USER_WARNING);
							}

							$this->handle_name_change($row['content_name'], $content_name);
						}

						if ($content_langname !== $row['content_langname'])
						{
							$this->handle_langname_change($content_name, $forum_id, $content_langname);
						}

						if ($set_permissions && !$row['req_permission'])
						{
							$this->handle_permissions('add', $content_name);
						}
						else if (!$set_permissions && $row['req_permission'])
						{
							$this->handle_permissions('remove', $content_name);
						}

						if ($content_status !== $row['content_status'])
						{
							$this->toggle_modules($content_name, $content_status);
						}
					}
					else
					{

						if ($this->content_type_exists($content_name))
						{
							$back_url = $this->u_action . '&amp;action=add_type';
							trigger_error($user->lang['CONTENT_MODE_EXISTS'] . adm_back_link($back_url), E_USER_WARNING);
						}

						$forum_id = $this->handle_content_forum('add', $content_langname);
						$this->handle_modules('add', $content_name, $content_langname, $content_status);

						if ($set_permissions)
						{
							$this->handle_permissions('add', $content_name);
						}
					}

					$summary_tpl = utf8_normalize_nfc(request_var('summary_tpl', '', true));
					$detail_tpl = utf8_normalize_nfc(request_var('detail_tpl', '', true));

					$sql_ary = array(
						'forum_id'				=> $forum_id,
						'content_name'			=> $content_name,
						'content_langname'		=> $content_langname,
						'content_status'		=> $content_status,
						'content_desc'			=> utf8_normalize_nfc(request_var('content_desc', '', true)),
						'req_approval'			=> request_var('req_approval', 1),
						'req_permission'		=> $set_permissions,
						'allow_comments'		=> request_var('allow_comments', 0),
						'allow_ratings'			=> request_var('allow_ratings', 0),
						'allow_keywords'		=> request_var('allow_tags', 0),
						'show_poster_info'		=> request_var('show_info', 0),
						'show_poster_contents'	=> request_var('show_contents', 0),
						'show_pagination'		=> request_var('show_pagination', 0),
						'items_per_page'		=> request_var('items_per_page', 0),
						'max_display'			=> request_var('max_display', 0),
						'char_limit'			=> request_var('char_limit', 0),
						'display_type'			=> $display_type,
						'summary_tpl'			=> $summary_tpl,
						'detail_tpl'			=> $detail_tpl,
						'content_desc_uid'		=> '',
						'content_desc_options'	=> 7,
						'content_desc_bitfield'	=> '',
					);

					// Get data for content description if specified
					if ($sql_ary['content_desc'])
					{
						generate_text_for_storage($sql_ary['content_desc'], $sql_ary['content_desc_uid'], $sql_ary['content_desc_bitfield'], $sql_ary['content_desc_options'], request_var('desc_parse_bbcode', false), request_var('desc_parse_urls', false), request_var('desc_parse_smilies', false));
					}

					// Must set at least one author/category
					if (!$sql_ary['max_display'])
					{
						$sql_ary['max_display'] = 1;
					}

					// Must set atleast one item
					if (!$sql_ary['items_per_page'])
					{
						$sql_ary['items_per_page'] = 1;
					}

					if ($content_id)
					{
						$db->sql_query('UPDATE ' . CONTENT_TYPES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE content_id = ' . (int) $content_id);
					}
					else
					{
						$db->sql_query('INSERT INTO ' . CONTENT_TYPES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary)); 
						$content_id = $db->sql_nextid();
					}

					$this->handle_content_fields($content_id, $fields_data, $optionables);

					$cache->destroy('_content_type_' . $content_name);

					if (!$message && $action != 'add_type')
					{
						meta_refresh(3, $this->u_action);
					}

					if ($action == 'add_type')
					{
						$u_set_permission = append_sid("{$phpbb_admin_path}index.$phpEx", 'i=permissions&mode=setting_forum_local&forum_id[]=' . $forum_id, true);
						$message = sprintf($user->lang['CONTENT_TYPE_CREATED'], '<a href="' . $u_set_permission . '">', '</a>') . '<br />' . $message;
					}
					else
					{
						$message = $user->lang['CONTENT_TYPE_UPDATED'] . '<br />' . $message;
					}

					trigger_error($message . adm_back_link($this->u_action));

				break;

				case 'delete':

					if (!$content_id)
					{
						trigger_error($user->lang['NO_CONTENT_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$transfer_to = request_var('xfer_to', '');

					$sql = 'SELECT content_name, req_permission
						FROM ' . CONTENT_TYPES_TABLE . ' 
						WHERE content_id = ' . (int) $content_id;
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$content_name = $row['content_name'];

					if (!$content_name)
					{
						trigger_error($user->lang['CONTENT_TYPE_NO_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// delete any permissions added by this content type
					if ($row['req_permission'])
					{
						$this->handle_permissions('remove', $content_name);
					}

					// Delete ucp/mcp modes
					$this->handle_modules('remove', $content_name);

					// Delete blocks that display this content type
					$sql = 'SELECT c.bid
						FROM ' . BLOCKS_TABLE . ' b, ' . BLOCKS_CONFIG_TABLE . " c
						WHERE b.bid = c.bid
							AND b.name " . $db->sql_like_expression($db->get_any_char() . 'content' . $db->get_any_char()) . "
							AND c.bvar = 'content_type'
							AND c.bval = '" . $db->sql_escape($content_name) . "'";
					$result = $db->sql_query($sql);

					$block_ids = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$block_ids[] = $row['bid'];
					}
					$db->sql_freeresult($result);

					if (sizeof($block_ids))
					{
						$phpbb_container->get('primetime.blocks.manager')->delete_blocks($block_ids);
					}

					$types_data = $content->get_all_types();
					$forum_id = $types_data[$content_name]['forum_id'];

					if ($transfer_to)
					{
						if (!isset($types_data[$transfer_to]))
						{
							trigger_error('NEW_TYPE_NO_EXIST');
						}

						$action_posts = 'move';
						$transfer_to_id = $types_data[$transfer_to]['forum_id'];
					}
					else
					{
						$action_posts = 'delete';
						$transfer_to_id = 0;

						$sql = 'SELECT topic_id
							FROM ' . TOPICS_TABLE . "
							WHERE content_type = '" . $db->sql_escape($module_mode) . "'";
						$result = $db->sql_query($sql);
	
						$topic_ids = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$topic_ids[] = $row['topic_id'];
						}
						$db->sql_freeresult($result);
					}

					$this->handle_content_forum('remove', '', $forum_id, $action_posts, $transfer_to_id);

					if ($transfer_to && $content_name)
					{
						$template->assign_vars(array(
							'S_POPUP'		=> true,
							'S_DELETE_TYPE'	=> true,
							'U_PATH'		=> append_sid(generate_board_url() . "/modules/content/acp_convert_type.$phpEx", "tag=$module_mode&amp;to=$transfer_to"))							
						);
					}
					else
					{	
						if (sizeof($topic_ids))
						{
							// TODO: trigger event here so other extensions can delete field data for this content type
						}

						// Delete the content type
						$sql = 'DELETE FROM ' . CONTENT_TYPES_TABLE . ' WHERE content_id = ' . (int) $content_id;
						$db->sql_query($sql);

						// Delete the content type fields
						$sql = 'DELETE FROM ' . CONTENT_FIELDS_TABLE . ' WHERE content_id = ' . (int) $content_id;
						$db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						trigger_error($user->lang['CONTENT_TYPE_DELETED'] . adm_back_link($this->u_action));

						$cache->destroy('_content_type_' . $content_name);
					}

				break;
			}
		}

		if ($action == 'enable' || $action == 'disable')
		{
			$content_type = request_var('type', '');
			$content_status = ($action == 'enable') ? 1 : 0;

			if (!$content_type)
			{
				trigger_error($user->lang['NO_MODULE'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$db->sql_query('UPDATE ' . CONTENT_TYPES_TABLE . " SET content_status = $content_status WHERE content_name = '" . $db->sql_escape($content_type) . "'");

			$this->toggle_modules($content_type, $content_status);
			$cache->destroy('_content_type_' . $content_type);
		}

		switch ($action)
		{
			case 'delete_type':

				if (!$content_id)
				{
					trigger_error($user->lang['NO_CONTENT_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = $db->sql_build_query('SELECT', array(
					'SELECT'			=> 'c.content_name, c.content_langname, COUNT(t.topic_id) AS total_topics',
					'FROM'				=> array(
						CONTENT_TYPES_TABLE		=> 'c',
					),
					'LEFT_JOIN'			=> array(
						array(
							'FROM'		=> array(TOPICS_TABLE => 't'),
							'ON'		=> 'c.content_name = t.content_type',
						),
					),
					'GROUP_BY'			=> 't.content_type',
					'WHERE'				=> 'c.content_id = ' . (int) $content_id)
				);
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['CONTENT_TYPE_NO_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$content_name = $row['content_name'];
				$total_topics = $row['total_topics'];
				$content_type = (isset($user->lang[$row['content_langname']])) ? $user->lang[$row['content_langname']] : $row['content_langname'];

				$content_types = $content->get_all_types();
				$content_data = $content->get_type($content_name);

				$field_types = $form->get_form_fields();
				$curr_fields = array_keys($content_data['content_fields']);

				$type_ops = '';
				if ($total_topics)
				{
					unset($content_types[$content_name]);
					$content_types = array_values($content_types);

					for ($i = 0, $size = sizeof($content_types); $i < $size; $i++)
					{
						$row = $content_types[$i];
						$fields_ary = $content->get_type($row['content_name']);

						$required_fields = array();
						foreach ($fields_ary as $field => $info)
						{
							if ($info['field_required'])
							{
								$required_fields[] = $field;
							}
						}

						if (!sizeof($required_fields) || (sizeof($required_fields) && !sizeof(array_diff($required_fields, $curr_fields))))
						{
							$langname = (isset($user->lang[$row['content_langname']])) ? $user->lang[$row['content_langname']] : $row['content_langname'];
							$type_ops .= '<option value="' . $row['content_name'] . '">' . $langname . '</option>';
						}
						unset($content_type[$i]);
					}

					$lang_var = (sizeof($content_types)) ? 'NO_COMPATIBLE_TYPES' : 'TYPE_NOT_TRANSFERABLE';
				}
				else
				{
					$lang_var = 'TYPE_NO_TOPICS';
				}

				$template->assign_vars(array(
					'CONTENT_ID'			=> $content_id,
					'CONTENT_TYPE_OPS'		=> $type_ops,
					'L_NO_COMPATIBLE_TYPES'	=> $user->lang[$lang_var],
					'L_CONFIRM_DELETE_TYPE'	=> sprintf($user->lang['CONFIRM_DELETE_TYPE'], $content_type),
					'U_ACTION'				=> $this->u_action . "&amp;action=delete&amp;t=$content_id")
				);

			break;

			case 'add_type':

				$action = 'edit_type';
				$summary_tpl = $detail_tpl = '';
				// no break

			case 'edit_type':

				$row['content_name'] = $row['content_desc'] = $row['content_langname'] = $row['display_type'] = '';
				$row['req_approval'] = $row['allow_comments'] = $row['allow_ratings'] = $row['allow_keywords'] = $row['show_pagination'] = 0;
				$row['show_poster_info'] = $row['show_poster_contents'] = $row['max_display'] = $row['content_status'] = 1;
				$row['items_per_page'] = 10;
				$row['char_limit'] = 100;

				$content_desc_data = array(
					'text'			=> '',
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true
				);

				$db_fields = array();
				$field_types = $form->get_form_fields();

				if ($content_id)
				{
					$sql = 'SELECT *
						FROM ' . CONTENT_FIELDS_TABLE . "
						WHERE content_id = $content_id
						ORDER BY field_order ASC";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						if (!isset($field_types[$row['field_type']]))
						{
							continue;
						}

						$l_type = $field_types[$row['field_type']]->get_name();
						decode_message($row['field_description'], $row['field_desc_uid']);

						$template->assign_block_vars('field', array(
							'NAME'			=> $row['field_name'],
							'TOKEN'			=> '{' . strtoupper($row['field_name']) . '}',
							'LABEL'			=> $row['field_label'],
							'DESCRIPTION'	=> $row['field_description'],
							'S_MOD_ONLY'	=> ($row['field_mod_only']) ? true : false,
							'S_SELECT'		=> ($row['field_type'] == 'select') ? true: false,
							'S_MULTY'		=> (bool) $row['field_multi'],
							'S_REQUIRED'	=> (bool) $row['field_required'],
							'S_LDISPLAY'	=> (bool) $row['field_ldisplay'],
							'S_TYPE'		=> $row['field_type'],
							'L_TYPE'		=> $l_type)
						);

						if (in_array($row['field_type'], $optionables))
						{
							$options_ary = explode(',', $row['field_options']);
							$options_ary = (sizeof($options_ary)) ? $options_ary : array('');

							foreach ($options_ary as $option)
							{
								$template->assign_block_vars('field.option', array(
									'CHECKED'	=> ($row['field_default'] == $option) ? true : false,
									'VALUE'		=> $option)
								);
							}
						}
					}
					$db->sql_freeresult($result);

					$sql = 'SELECT c.* 
						FROM ' . CONTENT_TYPES_TABLE . ' c 
						WHERE c.content_id = ' . (int) $content_id;
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// Parse description if specified
					if ($row['content_desc'])
					{
						if (!isset($row['content_desc_uid']))
						{
							// Before we are able to display the preview and plane text, we need to parse our request_var()'d value...
							$row['content_desc_uid'] = '';
							$row['content_desc_bitfield'] = '';
							$row['content_desc_options'] = 0;

							generate_text_for_storage($row['content_desc'], $row['content_desc_uid'], $row['content_desc_bitfield'], $row['content_desc_options'], request_var('desc_allow_bbcode', false), request_var('desc_allow_urls', false), request_var('desc_allow_smilies', false));
						}

						// decode...
						$content_desc_data = generate_text_for_edit($row['content_desc'], $row['content_desc_uid'], $row['content_desc_options']);
					}

					$summary_tpl = $row['summary_tpl'];
					$detail_tpl = $row['detail_tpl'];
				}

				$scripts = array(
					'js' => array(
            			'//ajax.googleapis.com/ajax/libs/jqueryui/' . JQUI_VERSION . '/jquery-ui.min.js',
            			'//d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js',
						$asset_path . 'ext/primetime/content/assets/js/content_admin.js',
					),
					'css'	=> array(
            			'//ajax.googleapis.com/ajax/libs/jqueryui/' . JQUI_VERSION . '/themes/base/jquery-ui.css',
						$asset_path . 'ext/primetime/content/assets/css/content_admin.css',
					)
				);

				if ($user->data['user_lang'] != 'en')
				{
					$user_lang = basename($user->data['user_lang']);
					$template->assign_var('S_EDITOR_LANG', $user_lang);
					//$scripts['js'][] = $phpbb_root_path . "modules/ckeditor/lang/$user_lang.js";
				}

				$primetime->add_assets($scripts);

				$template->assign_vars(array(
					'CONTENT_NAME'		=> $row['content_name'],
					'LANGNAME'			=> $row['content_langname'],
					'PER_PAGE'			=> $row['items_per_page'],
					'CHAR_LIMIT'		=> $row['char_limit'],
					'MAX_DISPLAY'		=> $row['max_display'],
					'CONTENT_DESC'		=> $content_desc_data['text'],
					'POST_AUTHOR'		=> $user->data['username'],
					'POST_DATE'			=> $user->format_date(time()),

					'S_ACTIVE'			=> $row['content_status'],
					'S_APPROVAL'		=> $row['req_approval'],
					'S_COMMENTS'		=> $row['allow_comments'],
					'S_RATINGS'			=> $row['allow_ratings'],
					'S_PAGINATION'		=> $row['show_pagination'],
					'S_PERMISSIONS'		=> $row['req_permission'],
					'S_DISPLAY'			=> $row['display_type'],
					'S_POSTER_INFO'		=> $row['show_poster_info'],
					'S_POSTER_CONTENTS'	=> $row['show_poster_contents'],
					'S_TYPE_OPS'		=> $this->get_field_options($field_types),

					'S_DESC_BBCODE_CHECKED'		=> ($content_desc_data['allow_bbcode']) ? true : false,
					'S_DESC_SMILIES_CHECKED'	=> ($content_desc_data['allow_smilies']) ? true : false,
					'S_DESC_URLS_CHECKED'		=> ($content_desc_data['allow_urls']) ? true : false,

					'U_ACTION'		=> $this->u_action . "&amp;action=edit_type&amp;t=$content_id")
				);

			break;

			default:

				$content_data = $content->get_all_types();

				$content_data = array_values($content_data);
				for ($i = 0, $size = sizeof($content_data); $i < $size; $i++)
				{
					$row = $content_data[$i];
					$url = $this->u_action . '&amp;t=' . $row['content_id'];
					$forum_id = $row['forum_id'];
					$langname = (isset($user->lang[$row['content_langname']])) ? $user->lang[$row['content_langname']] : $row['content_langname'];

					$template->assign_block_vars('types', array(
						'CONTENT_NAME'	=> $langname,

						'L_FORUM_PERMS'	=> sprintf($user->lang['EDIT_FORUM_PERMISSIONS'], $langname),
						'S_ENABLED'		=> $row['content_status'],

						'U_DELETE'		=> $url . '&amp;action=delete_type',
						'U_EDIT'		=> $url . '&amp;action=edit_type',
						'U_ENABLE'		=> $url . '&amp;action=enable&amp;type=' . $row['content_name'],
						'U_DISABLE'		=> $url . '&amp;action=disable&amp;type=' . $row['content_name'],
						//'U_VIEW'		=> append_sid("{$phpbb_root_path}app.$phpEx", "i=content&amp;mode=$module_mode"),
						//'U_POST'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", "i=content&amp;mode=$module_mode&amp;action=post"),
						'U_GROUP_PERMS'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=permissions&mode=setting_group_global"),
						'U_FORUM_PERMS'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=permissions&mode=setting_forum_local&forum_id[]=$forum_id"))
					);
				}

				$action = 'ACP_CONTENT';
				$template->assign_vars(array('U_ADD_TYPE'	=> $this->u_action . "&amp;action=add_type"));

			break;
		}

		$l_action = strtoupper($action);
		$template->assign_vars(array('S_' . $l_action => true));

		$template->set_filenames(array(
			'content' => 'acp_content.html')
		);

		$this->tpl_name = 'acp_content';
		$this->page_title = $l_action;
	}

	function get_field_options($field_types)
	{
		$options = '';
		foreach ($field_types as $service => $driver)
		{
			$options .= '<option value="' . $service . '">' . $driver->get_name() . "</option>\n";
		}

		return $options;
	}

	function content_type_exists($content_name)
	{
		global $db;

		$sql = 'SELECT content_id
			FROM ' . CONTENT_TYPES_TABLE . "
			WHERE content_name = '" . $db->sql_escape($content_name) . "'";
		$result = $db->sql_query_limit($sql, 1);
		$content_id = $db->sql_fetchfield('content_id');
		$db->sql_freeresult($result);

		return ($content_id) ? true : false;
	}

	function handle_name_change($old_name, $new_name)
	{
		global $db, $user;

		// update mcp/ucp module modes
		$module_data = array(
			'module_mode'	=> $new_name,
		);
		$this->update_modules($old_name, $module_data, 'ucp');
		$this->update_modules($old_name, $module_data, 'mcp');

		// update permissions
		$this->update_permissions($old_name, $new_name);

		// Update topics
		$sql = 'UPDATE ' . TOPICS_TABLE . " SET content_type = '$new_name' WHERE content_type = '$old_name'";
		$db->sql_query($sql);
	}

	function handle_langname_change($name, $forum_id, $new_langname)
	{
		global $db, $user;

		// update content forum name
		$forum_name = (isset($user->lang[$new_langname])) ? $user->lang[$new_langname] : $new_langname;
		$sql = 'UPDATE ' . FORUMS_TABLE . " SET forum_name = '$forum_name' WHERE forum_id = " . (int) $forum_id;
		$db->sql_query($sql);

		// update mcp/ucp module langname
		$module_data = array('module_langname' => $new_langname);
		$this->update_modules($name, $module_data, 'mcp');
		$this->update_modules($name, $module_data, 'ucp');
	}

	function handle_permissions($mode, $name)
	{
		global $phpbb_container;

		$migrator = $phpbb_container->get('migrator.tool.permission');

		$options_ary = array(
			'u_content_view_' . $name, 
			'u_content_post_' . $name, 
			'm_content_manage_' . $name,
		);

		$auth_base = array(
			'ucp' => 'ext_primetime/content', 
			'mcp' => 'ext_primetime/content'
		);

		if ($mode == 'add')
		{
			$auth_base['ucp'] .= ' && acl_u_content_post_' . $name;
			$auth_base['mcp'] .= ' && acl_m_content_manage_' . $name;
		}

		foreach ($options_ary as $option)
		{
			$migrator->$mode($option);
		}
		
		$migrator->permission_set('ROLE_ADMIN_STANDARD', $options_ary, 'role', ($mode == 'add') ? true : false);

		// update mcp/ucp module modes
		foreach ($auth_base as $class => $permissions)
		{
			$module_data = array(
				'module_auth'	=> $permissions
			);
			$this->update_modules($name, $module_data, $class);
		}
	}

	function update_permissions($old_name, $new_name)
	{
		global $cache, $db;

		$options_ary = array('u_content_view_', 'u_content_post_', 'm_content_manage_');

		foreach ($options_ary as $option)
		{
			$old_option = $db->sql_escape($option . $old_name);
			$new_option = $db->sql_escape($option . $new_name);

			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . " SET auth_option = '$new_option' WHERE auth_option = '$old_option'";
			$db->sql_query($sql);
		}
		$cache->destroy('_acl_options');
	}

	function handle_modules($mode, $name, $langname = '', $enabled = true)
	{
		global $phpbb_container;

		$migrator = $phpbb_container->get('migrator.tool.module');

		switch ($mode)
		{
			case 'add':

				$auth_base = array(
					'ucp' => 'ext_primetime/content', 
					'mcp' => 'ext_primetime/content'
				);

				foreach ($auth_base as $class => $permissions)
				{
					$module_data = array(
						'module_mode'		=> $name,
						'module_langname'	=> $langname,
						'module_enabled'    => (bool) $enabled,
						'module_display'    => 1,
						'module_class'      => $class,
						'module_auth'       => $permissions,
						'module_basename'	=> '\primetime\content\\' . $class . '\content_module',
					);

					$migrator->add($class, 'CONTENT_CP', $module_data);
				}
			break;

			case 'remove':
				$migrator->remove('ucp', 'CONTENT_CP', $name);
				$migrator->remove('mcp', 'CONTENT_CP', $name);
			break;
		}
	}

	function toggle_modules($module_mode, $status)
	{
		$module_data = array('module_enabled' => $status);
		$this->update_modules($module_mode, $module_data, 'mcp');
		$this->update_modules($module_mode, $module_data, 'ucp');
	}

	function update_modules($module_mode, $module_data, $class)
	{
		global $cache, $db;

		$sql = 'UPDATE ' . MODULES_TABLE . ' 
			SET ' . $db->sql_build_array('UPDATE', $module_data) .  "
			WHERE module_mode = '$module_mode'
				AND module_basename = '" . $db->sql_escape("\primetime\content\\$class\content_module") . "'
				AND module_class = '$class'";
		$db->sql_query($sql);

		$cache->destroy('_modules_' . $class);

		// Additionally remove sql cache
		$cache->destroy('sql', MODULES_TABLE);
	}

	function handle_content_forum($mode, $content_langname = '', $forum_id = 0, $action_posts = 'delete', $transfer_to_id = 0)
	{
		global $config, $phpbb_container, $user;

		$forum = $phpbb_container->get('primetime.forum.manager');

		switch ($mode)
		{
			case 'add':
				$forum_data = array(
					'forum_type'	=> FORUM_POST,
					'forum_name'	=> (isset($user->lang[$content_langname])) ? $user->lang[$content_langname] : $content_langname,
					'forum_desc'	=> '',
					'parent_id'		=> $config['primetime_content_forum_id'],
				);

				$errors = $forum->add($forum_data);

				if (!$forum_data['forum_id'])
				{
					trigger_error('NO_FORUM_ID');
				}

				return (int) $forum_data['forum_id'];

			break;

			case 'remove':
				$forum->delete_forum($forum_id, $action_posts, true, $transfer_to_id);
			break;
		}
	}

	function handle_content_fields($content_id, $fields_data, $optionables)
	{
		global $db;

		$fields_ary = array_keys($fields_data);
		$count = 0;

		foreach ($fields_ary as $field)
		{
			$row = $fields_data[$field];

			$multi = 0;
			$field_options = $default = $uid = $bitfield = $options = '';

			if (in_array($row['type'], $optionables))
			{
				$default = (!empty($row['default'])) ? $row['default'] : '';
				$field_options = join(',', utf8_normalize_nfc(request_var($field . '_options', array(''), true)));
				$multi = ($row['type'] == 'select' && !empty($row['multi'])) ? $row['multi'] : 0;
			}

			generate_text_for_storage($row['description'], $uid, $bitfield, $options);

			$form_fields[$field] = array(
				'content_id'			=> $content_id,
				'field_name'			=> $row['name'],
				'field_label'			=> $row['label'],
				'field_type'			=> $row['type'],
				'field_options'			=> $field_options,
				'field_default'			=> $default,
				'field_multi'			=> (int) $multi,
				'field_mod_only'		=> (int) $row['input'],
				'field_required'		=> (int) $row['required'],
				'field_ldisplay'		=> (int) $row['ldisplay'],
				'field_description'		=> $row['description'],
				'field_desc_uid'		=> $uid,
				'field_desc_bitfield'	=> $bitfield,
				'field_desc_options'	=> $options,
				'field_order'			=> $count,
			);
			$count++;
		}

		$form_fields = array_values($form_fields);

		$db->sql_query('DELETE FROM ' . CONTENT_FIELDS_TABLE . ' WHERE content_id = ' . (int) $content_id);
		$db->sql_multi_insert(CONTENT_FIELDS_TABLE, $form_fields);
	}
}
