<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\acp;

class content_module
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \primetime\content\services\form\builder */
	protected $form;

	/** @var \primetime\primetime\core\forum\query */
	protected $forum;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	public $u_action;

	public function __construct()
	{
		global $auth, $cache, $config, $db, $request, $template, $user, $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_container;

		$this->auth		= $auth;
		$this->cache	= $cache;
		$this->config	= $config;
		$this->db		= $db;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
		$this->helper			= $phpbb_container->get('controller.helper');
		$this->content			= $phpbb_container->get('primetime.content.types');
		$this->form				= $phpbb_container->get('primetime.content.form.builder');
		$this->forum			= $phpbb_container->get('primetime.primetime.forum.manager');
		$this->primetime		= $phpbb_container->get('primetime.primetime.util');
		$this->phpbb_admin_path	= $phpbb_admin_path;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $phpEx;

		$this->content_fields_table	= $phpbb_container->getParameter('tables.primetime.content_fields');
		$this->content_types_table	= $phpbb_container->getParameter('tables.primetime.content_types');

		$this->load_views($phpbb_container->get('primetime.content.views_collection'));
	}

	public function load_views($views)
	{
		foreach ($views as $service => $view)
		{
			$this->views[$service] = $view->get_langname();
		}
	}

	public function main($id, $mode)
	{
		$action = $this->request->variable('action', '');
		$content_type = $this->request->variable('type', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		if ($submit)
		{
			switch ($action)
			{
				case 'add':
				case 'edit':

					$content_id = 0;
					$action = $message = '';
					$fields_data = utf8_normalize_nfc($this->request->variable('fdata', array('' => array('' => ''))));
					$content_name = str_replace(' ', '_', strtolower(trim($this->request->variable('content_name', ''))));
					$content_langname = utf8_normalize_nfc($this->request->variable('content_langname', '', true));
					$content_enabled = $this->request->variable('content_enabled', 1);
					$group_by = $this->request->variable('group_by', '');
					$forum_perm_from = $this->request->variable('copy_forum_perm', 0);

					//Let's do some checks
					if (!$content_name)
					{
						trigger_error($this->user->lang['MISSING_CONTENT_NAME'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (!$content_langname)
					{
						trigger_error($this->user->lang['MISSING_CONTENT_LANGNAME'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (!sizeof($fields_data))
					{
						trigger_error($this->user->lang['NO_CONTENT_FIELDS'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if ($content_type)
					{
						$row = $this->content->get_type($content_type);

						$forum_id = (int) $row['forum_id'];
						$content_id = (int) $row['content_id'];

						// Copy permissions?
						if ($forum_perm_from && $forum_perm_from != $forum_id)
						{
							copy_forum_permissions($forum_perm_from, $forum_id, false, false);
							phpbb_cache_moderators($this->db, $this->cache, $this->auth);

							$this->auth->acl_clear_prefetch();
							$this->cache->destroy('sql', FORUMS_TABLE);
						}

						if ($content_name !== $row['content_name'] && $this->content_type_exists($content_name))
						{
							$back_url = $this->u_action . '&amp;action=edit&amp;t=' . $content_id;
							trigger_error(sprintf($this->user->lang['CONTENT_NAME_EXISTS'], $content_name) . adm_back_link($back_url), E_USER_WARNING);
						}

						if ($content_langname !== $row['content_langname'])
						{
							$this->handle_langname_change($content_name, $forum_id, $content_langname);
						}
					}
					else
					{
						if ($this->content_type_exists($content_name))
						{
							$back_url = $this->u_action . '&amp;action=add';
							trigger_error(sprintf($this->user->lang['CONTENT_NAME_EXISTS'], $content_name) . adm_back_link($back_url), E_USER_WARNING);
						}

						$forum_id = $this->handle_content_forum('add', $content_langname, 0, $forum_perm_from);
					}

					$summary_tpl = utf8_normalize_nfc($this->request->variable('summary_tpl', '', true));
					$detail_tpl = utf8_normalize_nfc($this->request->variable('detail_tpl', '', true));

					$sql_ary = array(
						'forum_id'				=> $forum_id,
						'content_name'			=> $content_name,
						'content_langname'		=> $content_langname,
						'content_enabled'		=> $content_enabled,
						'content_colour'		=> substr(md5($content_name), 0, 6),
						'content_desc'			=> utf8_normalize_nfc($this->request->variable('content_desc', '', true)),
						'content_desc_uid'		=> '',
						'content_desc_options'	=> 7,
						'content_desc_bitfield'	=> '',
						'req_approval'			=> $this->request->variable('req_approval', 1),
						'allow_comments'		=> $this->request->variable('allow_comments', 0),
						'show_poster_info'		=> $this->request->variable('show_info', 0),
						'show_poster_contents'	=> $this->request->variable('show_contents', 0),
						'show_pagination'		=> $this->request->variable('show_pagination', 0),
						'index_show_desc'		=> $this->request->variable('index_show_desc', 0),
						'items_per_page'		=> $this->request->variable('items_per_page', 0),
						'topics_per_group'		=> $this->request->variable('topics_per_group', 0),
						'display_type'			=> $this->request->variable('display_type', ''),
						'summary_tpl'			=> $summary_tpl,
						'detail_tpl'			=> $detail_tpl,
						'last_modified'			=> time(),
					);

					// Get data for content description if specified
					if ($sql_ary['content_desc'])
					{
						generate_text_for_storage($sql_ary['content_desc'], $sql_ary['content_desc_uid'], $sql_ary['content_desc_bitfield'], $sql_ary['content_desc_options'], $this->request->variable('desc_parse_bbcode', false), $this->request->variable('desc_parse_urls', false), $this->request->variable('desc_parse_smilies', false));
					}

					// Must set at least one topic per author/category
					if (!$sql_ary['topics_per_group'])
					{
						$sql_ary['topics_per_group'] = 1;
					}

					// Must set atleast one item
					if (!$sql_ary['items_per_page'])
					{
						$sql_ary['items_per_page'] = 1;
					}

					if ($content_id)
					{
						$this->db->sql_query('UPDATE ' . $this->content_types_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE content_id = ' . (int) $content_id);
					}
					else
					{
						$this->db->sql_query('INSERT INTO ' . $this->content_types_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
						$content_id = $this->db->sql_nextid();
					}

					$this->handle_content_fields($content_id, $fields_data);
					$this->cache->destroy('_content_types');

					if (!$message && $action != 'add')
					{
						meta_refresh(3, $this->u_action);
					}

					if ($action == 'add')
					{
						$u_set_permission = append_sid("{$this->phpbb_admin_path}index.$this->php_ext", 'i=permissions&mode=setting_forum_local&forum_id[]=' . $forum_id, true);
						$message = sprintf($this->user->lang['CONTENT_TYPE_CREATED'], '<a href="' . $u_set_permission . '">', '</a>') . '<br />' . $message;
					}
					else
					{
						$message = $this->user->lang['CONTENT_TYPE_UPDATED'] . '<br />' . $message;
					}

					trigger_error($message . adm_back_link($this->u_action));

				break;

				case 'delete':

					if (!$content_type)
					{
						trigger_error($this->user->lang['NO_CONTENT_TYPE'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$transfer_to = $this->request->variable('xfer_to', '');

					$types_data = $this->content->get_all_types();
					$row = $this->content->get_type($content_type);

					$forum_id = (int) $row['forum_id'];
					$content_name = $row['content_name'];

					if (!$content_name)
					{
						trigger_error($this->user->lang['CONTENT_TYPE_NO_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					// Delete ucp/mcp modes
					$this->handle_modules('remove', $content_name);

					// Delete blocks that display this content type
					$sql = 'SELECT c.bid
						FROM ' . BLOCKS_TABLE . ' b, ' . BLOCKS_CONFIG_TABLE . " c
						WHERE b.bid = c.bid
							AND b.name " . $this->db->sql_like_expression($this->db->get_any_char() . 'content' . $this->db->get_any_char()) . "
							AND c.bvar = 'content_type'
							AND c.bval = '" . $this->db->sql_escape($content_name) . "'";
					$result = $this->db->sql_query($sql);

					$block_ids = array();
					while ($row = $this->db->sql_fetchrow($result))
					{
						$block_ids[] = $row['bid'];
					}
					$this->db->sql_freeresult($result);

					if (sizeof($block_ids))
					{
						$phpbb_container->get('primetime.blocks.manager')->delete_blocks($block_ids);
					}

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
							WHERE content_type = '" . $this->db->sql_escape($module_mode) . "'";
						$result = $this->db->sql_query($sql);

						$topic_ids = array();
						while ($row = $this->db->sql_fetchrow($result))
						{
							$topic_ids[] = $row['topic_id'];
						}
						$this->db->sql_freeresult($result);
					}

					$this->handle_content_forum('remove', '', $forum_id, 0, $action_posts, $transfer_to_id);

					if ($transfer_to && $content_name)
					{
						$this->template->assign_vars(array(
							'S_POPUP'		=> true,
							'S_DELETE_TYPE'	=> true,
							'U_PATH'		=> append_sid(generate_board_url() . "/modules/content/acp_convert_type.$this->php_ext", "tag=$module_mode&amp;to=$transfer_to")
						));
					}
					else
					{
						if (sizeof($topic_ids))
						{
							// TODO: trigger event here so other extensions can delete field data for this content type
						}

						// Delete the content type
						$sql = 'DELETE FROM ' . $this->content_types_table . ' WHERE content_id = ' . (int) $content_id;
						$this->db->sql_query($sql);

						// Delete the content type fields
						$sql = 'DELETE FROM ' . $this->content_fields_table . ' WHERE content_id = ' . (int) $content_id;
						$this->db->sql_query($sql);

						meta_refresh(3, $this->u_action);
						trigger_error($this->user->lang['CONTENT_TYPE_DELETED'] . adm_back_link($this->u_action));

						$this->cache->destroy('_content_types');
					}

				break;
			}
		}

		if ($action == 'enable' || $action == 'disable')
		{
			$content_enabled = ($action == 'enable') ? 1 : 0;

			if (!$content_type)
			{
				trigger_error($this->user->lang['NO_CONTENT_TYPE'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->db->sql_query('UPDATE ' . $this->content_types_table . " SET content_enabled = $content_enabled WHERE content_name = '" . $this->db->sql_escape($content_type) . "'");
			$this->cache->destroy('_content_types');
		}

		switch ($action)
		{
			case 'delete':

				if (!$content_type)
				{
					trigger_error($this->user->lang['NO_CONTENT_TYPE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$row = $this->content->get_type($content_type);

				if (sizeof($content_data))
				{
					trigger_error($this->user->lang['CONTENT_TYPE_NO_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$forum_id = (int) $row['forum_id'];

				$sql = 'SELECT COUNT(t.topic_id) AS total_topics
					FROM ' . TOPICS_TABLE . "
					WHERE forum_id = $forum_id
						AND content_type = '" . $this->db->sql_escape($content_type) . "'";
				$result = $this->db->sql_query($sql);
				$total_topics = $this->db->sql_fetchfield('total_topics');
				$this->db->sql_freeresult($result);

				$l_content_type = (isset($this->user->lang[$row['content_langname']])) ? $this->user->lang[$row['content_langname']] : $row['content_langname'];

				$type_ops = '';
				if ($total_topics)
				{
					$content_types = array_keys($this->content->get_all_types());
					$curr_fields = array_keys($row['content_fields']);
					unset($content_types[$content_name]);

					foreach ($content_types as $type)
					{
						$type_data = $this->content->get_type($type);
						$type_fields = $type_data['content_fields'];

						$required_fields = array();
						foreach ($type_fields as $field => $info)
						{
							if ($info['field_required'])
							{
								$required_fields[] = $field;
							}
						}

						if (!sizeof($required_fields) || (sizeof($required_fields) && !sizeof(array_diff($required_fields, $curr_fields))))
						{
							$langname = (isset($this->user->lang[$type_data['content_langname']])) ? $this->user->lang[$type_data['content_langname']] : $type_data['content_langname'];
							$type_ops .= '<option value="' . $type_data['content_name'] . '">' . $langname . '</option>';
						}
					}

					$lang_var = (sizeof($content_types)) ? 'NO_COMPATIBLE_TYPES' : 'TYPE_NOT_TRANSFERABLE';
				}
				else
				{
					$lang_var = 'TYPE_NO_TOPICS';
				}

				$this->template->assign_vars(array(
					'CONTENT_TYPE'			=> $content_type,
					'CONTENT_TYPE_OPS'		=> $type_ops,
					'L_NO_COMPATIBLE_TYPES'	=> $this->user->lang[$lang_var],
					'L_CONFIRM_DELETE_TYPE'	=> sprintf($this->user->lang['CONFIRM_DELETE_TYPE'], $l_content_type),
					'U_ACTION'				=> $this->u_action . "&amp;action=delete&amp;type=$content_type")
				);

			break;

			case 'add':

				$action = 'edit';
				$forum_id = 0;
				$row = array(
					'content_name'			=> '',
					'content_langname'		=> '',
					'content_enabled'		=> 1,
					'content_desc'			=> '',
					'req_approval'			=> 0,
					'allow_comments'		=> 1,
					'show_poster_info'		=> 0,
					'show_poster_contents'	=> 0,
					'show_pagination'		=> 1,
					'index_show_desc'		=> 0,
					'items_per_page'		=> 10,
					'topics_per_group'		=> 1,
					'display_type'			=> 'portal',
					'summary_tpl'			=> '',
					'detail_tpl'			=> '',
					'content_desc_uid'		=> '',
					'content_desc_options'	=> 7,
					'content_desc_bitfield'	=> '',
				);
				// no break

			case 'edit':

				$content_desc_data = array(
					'text'			=> '',
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true
				);

				$db_fields = array();
				$field_types = $this->form->get_form_fields();

				if ($content_type)
				{
					$row = $this->content->get_type($content_type);

					$forum_id = (int) $row['forum_id'];
					$content_fields = (array) $row['content_fields'];

					foreach ($content_fields as $data)
					{
						if (!isset($field_types[$data['field_type']]))
						{
							continue;
						}

						$l_type = $field_types[$data['field_type']]->get_langname();
						decode_message($data['field_explain'], $data['field_exp_uid']);

						$data += array(
							'TOKEN'			=> '{' . strtoupper($data['field_name']) . '}',
							'TYPE_LABEL'	=> $l_type,
							'DEFAULT_TYPE'	=> ($data['field_type'] == 'checkbox' || ($data['field_type'] == 'select' && $data['field_multi'])) ? 'checkbox' : 'radio',
						);

						$this->template->assign_block_vars('field', array_change_key_case($data, CASE_UPPER));

						if (isset($data['field_options']))
						{
							$selected = array();
							if (isset($data['field_value']))
							{
								$selected = array_flip($data['field_value']);
							}

							foreach ($data['field_options'] as $option)
							{
								$this->template->assign_block_vars('field.option', array(
									'VALUE'		=> $option,
									'S_CHECKED'	=> (isset($selected[$option])) ? true : false
								));
							}
						}
					}

					// Parse description if specified
					if ($row['content_desc'])
					{
						if (!isset($row['content_desc_uid']))
						{
							// Before we are able to display the preview and plane text, we need to parse our $this->request->variable()'d value...
							$row['content_desc_uid'] = '';
							$row['content_desc_bitfield'] = '';
							$row['content_desc_options'] = 0;

							generate_text_for_storage($row['content_desc'], $row['content_desc_uid'], $row['content_desc_bitfield'], $row['content_desc_options'], $this->request->variable('desc_allow_bbcode', false), $this->request->variable('desc_allow_urls', false), $this->request->variable('desc_allow_smilies', false));
						}

						// decode...
						$content_desc_data = generate_text_for_edit($row['content_desc'], $row['content_desc_uid'], $row['content_desc_options']);
					}

					$summary_tpl = $row['summary_tpl'];
					$detail_tpl = $row['detail_tpl'];
				}

				$asset_path = $this->primetime->asset_path;
				$this->primetime->add_assets(array(
					'js' => array(
						'//ajax.googleapis.com/ajax/libs/jqueryui/' . JQUI_VERSION . '/jquery-ui.min.js',
						'//d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js',
						$asset_path . 'ext/primetime/primetime/components/twig.js/twig.min.js',
						'@primetime_content/assets/content_admin.min.js',
					),
					'css'	=> array(
						'//ajax.googleapis.com/ajax/libs/jqueryui/' . JQUI_VERSION . '/themes/smoothness/jquery-ui.css',
						'@primetime_content/assets/content_admin.min.css',
					)
				));

				foreach ($this->views as $service => $label)
				{
					$this->template->assign_block_vars('view', array(
						'LABEL'		=> (isset($this->user->lang[$label])) ? $this->user->lang[$label] : $label,
						'VALUE'		=> $service,
						'S_SELECTED'	=> ($service == $row['display_type']) ? true : false,
					));
				}

				$this->template->assign_vars(array(
					'CONTENT_NAME'		=> $row['content_name'],
					'LANGNAME'			=> $row['content_langname'],
					'ITEMS_PER_PAGE'	=> $row['items_per_page'],
					'TOPICS_PER_GROUP'	=> $row['topics_per_group'],
					'CONTENT_DESC'		=> $content_desc_data['text'],
					'POST_AUTHOR'		=> $this->user->data['username'],
					'POST_DATE'			=> $this->user->format_date(time()),

					'S_ENABLED'			=> $row['content_enabled'],
					'S_APPROVAL'		=> $row['req_approval'],
					'S_COMMENTS'		=> $row['allow_comments'],
					'S_DETAIL_TPL'		=> $row['detail_tpl'],
					'S_SUMMARY_TPL'		=> $row['summary_tpl'],
					'S_PAGINATION'		=> $row['show_pagination'],
					'S_DISPLAY'			=> $row['display_type'],
					'S_POSTER_INFO'		=> $row['show_poster_info'],
					'S_POSTER_CONTENTS'	=> $row['show_poster_contents'],
					'S_TYPE_OPS'		=> $this->get_field_options($field_types),
					'S_FORUM_OPTIONS'	=> make_forum_select(false, ($action == 'edit') ? $forum_id : false, true, false, false),
					'S_INDEX_SHOW_DESC'	=> $row['index_show_desc'],

					'S_CAN_COPY_PERMISSIONS'	=> ($action != 'edit' || empty($forum_id) || ($this->auth->acl_get('a_fauth') && $this->auth->acl_get('a_authusers') && $this->auth->acl_get('a_authgroups') && $this->auth->acl_get('a_mauth'))) ? true : false,
					'S_DESC_BBCODE_CHECKED'		=> ($content_desc_data['allow_bbcode']) ? true : false,
					'S_DESC_SMILIES_CHECKED'	=> ($content_desc_data['allow_smilies']) ? true : false,
					'S_DESC_URLS_CHECKED'		=> ($content_desc_data['allow_urls']) ? true : false,

					'U_ACTION'		=> $this->u_action . "&amp;action=edit&amp;type=$content_type")
				);

			break;

			default:

				$content_data = $this->content->get_all_types();

				$content_data = array_values($content_data);
				for ($i = 0, $size = sizeof($content_data); $i < $size; $i++)
				{
					$row = $content_data[$i];
					$forum_id = $row['forum_id'];
					$type = $row['content_name'];
					$langname = (isset($this->user->lang[$row['content_langname']])) ? $this->user->lang[$row['content_langname']] : $row['content_langname'];
					$u_content_type = $this->helper->route('primetime_content_index', array(
						'type'	=> $type,
					));

					$this->template->assign_block_vars('types', array(
						'CONTENT_TYPE'	=> $langname,

						'L_FORUM_PERMS'	=> sprintf($this->user->lang['EDIT_FORUM_PERMISSIONS'], $langname),
						'S_ENABLED'		=> $row['content_enabled'],

						'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;type=' . $row['content_name'],
						'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;type=' . $row['content_name'],
						'U_ENABLE'		=> $this->u_action . '&amp;action=enable&amp;type=' . $row['content_name'],
						'U_DISABLE'		=> $this->u_action . '&amp;action=disable&amp;type=' . $row['content_name'],
						'U_VIEW'		=> $u_content_type,
						'U_POST'		=> append_sid("{$this->phpbb_root_path}ucp." . $this->php_ext, "i=-primetime-content-ucp-content_module&amp;mode=content&amp;action=post&amp;type={$type}"),
						'U_GROUP_PERMS'	=> append_sid("{$this->phpbb_admin_path}index." . $this->php_ext, "i=acp_permissions&amp;mode=setting_group_global"),
						'U_FORUM_PERMS'	=> append_sid("{$this->phpbb_admin_path}index." . $this->php_ext, "i=acp_permissions&amp;mode=setting_forum_local&amp;forum_id[]=$forum_id"))
					);
				}

				$l_action = 'ACP_CONTENT';
				$this->template->assign_vars(array('U_ADD_TYPE'	=> $this->u_action . "&amp;action=add"));

			break;
		}

		$this->template->assign_vars(array('S_' . strtoupper($action) => true));

		$this->template->set_filenames(array(
			'content' => 'acp_content.html')
		);

		$this->tpl_name = 'acp_content';
		$this->page_title = 'CONTENT_TYPES';
	}

	public function get_field_options($field_types)
	{
		unset($field_types['reset']);
		unset($field_types['submit']);

		$options = '';
		foreach ($field_types as $service => $driver)
		{
			$options .= '<option value="' . $driver->get_name() . '">' . $driver->get_langname() . "</option>\n";
		}

		return $options;
	}

	public function content_type_exists($content_name)
	{
		$sql = 'SELECT content_id
			FROM ' . $this->content_types_table . "
			WHERE content_name = '" . $this->db->sql_escape($content_name) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$content_id = $this->db->sql_fetchfield('content_id');
		$this->db->sql_freeresult($result);

		return ($content_id) ? true : false;
	}

	public function handle_langname_change($name, $forum_id, $new_langname)
	{
		// update content forum name
		$forum_name = (isset($this->user->lang[$new_langname])) ? $this->user->lang[$new_langname] : $new_langname;
		$sql = 'UPDATE ' . FORUMS_TABLE . " SET forum_name = '$forum_name' WHERE forum_id = " . (int) $forum_id;
		$this->db->sql_query($sql);
	}

	public function handle_content_forum($mode, $content_langname = '', $forum_id = 0, $forum_perm_from = 0, $action_posts = 'delete', $transfer_to_id = 0)
	{
		switch ($mode)
		{
			case 'add':
				$forum_data = array(
					'forum_type'	=> FORUM_POST,
					'forum_name'	=> (isset($this->user->lang[$content_langname])) ? $this->user->lang[$content_langname] : $content_langname,
					'forum_desc'	=> '',
					'parent_id'		=> $this->config['primetime_content_forum_id'],
				);

				$errors = $this->forum->add($forum_data, $forum_perm_from);

				if (!$forum_data['forum_id'])
				{
					trigger_error('NO_FORUM_ID');
				}

				return (int) $forum_data['forum_id'];

			break;

			case 'remove':
				$this->forum->delete_forum($forum_id, $action_posts, true, $transfer_to_id);
			break;
		}
	}

	public function handle_content_fields($content_id, $fields_data)
	{
		$fields_settings = utf8_normalize_nfc($this->request->variable('fsettings', array('' => array('' => ''))));
		$fields_defaults = utf8_normalize_nfc($this->request->variable('fdefaults', array('' => array('' => ''))));

		$count = 0;
		$fields_ary = array_filter(array_keys($fields_data));

		foreach ($fields_ary as $field)
		{
			$row = $fields_data[$field];

			$field_options = utf8_normalize_nfc($this->request->variable($field . '_options', array(''), true));

			$uid = $bitfield = $options = $settings = '';
			generate_text_for_storage($row['description'], $uid, $bitfield, $options);

			if (sizeof($field_options))
			{
				$fields_settings[$field]['field_options'] = array_combine($field_options, $field_options);
			}

			if (isset($fields_defaults[$field]))
			{
				$fields_settings[$field]['field_value'] = $fields_defaults[$field];
			}

			if (isset($fields_settings[$field]))
			{
				$settings = serialize($fields_settings[$field]);
			}

			$form_fields[$field] = array(
				'content_id'			=> $content_id,
				'field_name'			=> $row['name'],
				'field_label'			=> $row['label'],
				'field_type'			=> $row['type'],
				'field_settings'		=> $settings,
				'field_mod_only'		=> (int) $row['input'],
				'field_required'		=> (int) $row['required'],
				'field_summary_show'	=> (isset($row['summary_show'])) ? true : false,
				'field_detail_show'		=> (isset($row['detail_show'])) ? true : false,
				'field_summary_ldisp'	=> (int) $row['summary_ldisp'],
				'field_detail_ldisp'	=> (int) $row['detail_ldisp'],
				'field_explain'			=> $row['description'],
				'field_exp_uid'			=> $uid,
				'field_exp_bitfield'	=> $bitfield,
				'field_exp_options'		=> $options,
				'field_order'			=> $count,
			);
			$count++;
		}

		$form_fields = array_values($form_fields);

		$this->db->sql_query('DELETE FROM ' . $this->content_fields_table . ' WHERE content_id = ' . (int) $content_id);
		$this->db->sql_multi_insert($this->content_fields_table, $form_fields);
	}
}
