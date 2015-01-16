<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services;

use Symfony\Component\DependencyInjection\Container;
use Cocur\Slugify\Slugify;

class manager
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var Container */
	protected $phpbb_container;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \primetime\cotent\services\comments */
	protected $comments;

	/* @var \primetime\content\services\displayer */
	protected $displayer;

	/** @var \primetime\content\services\form\builder */
	protected $form;

	/** @var \primetime\primetime\core\forum\query */
	protected $forum;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\cache\service						$cache				Cache object
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\content_visibility					$content_visibility	Content visibility
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\controller\helper					$helper				Helper object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param Container									$phpbb_container	Service container
	 * @param \phpbb\request\request_interface			$request			Request object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\user								$user				User object
	 * @param \primetime\cotent\services\comments		$comments			Comments object
	 * @param \primetime\content\services\displayer		$displayer			Content displayer object
	 * @param \primetime\content\services\form\builder	$form				Form object
	 * @param \primetime\primetime\core\forum\query		$forum				Forum object
	 * @param string									$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, Container $phpbb_container, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \primetime\content\services\comments $comments, \primetime\content\services\displayer $displayer, \primetime\content\services\form\builder $form, \primetime\primetime\core\forum\query $forum, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->phpbb_container = $phpbb_container;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->comments = $comments;
		$this->displayer = $displayer;
		$this->form = $form;
		$this->forum = $forum;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function get_post_data($forum_id, $topic_id)
	{
		$sql = 'SELECT f.*, t.*, p.*, u.username, u.username_clean, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . " u
			WHERE t.topic_id = $topic_id
				AND t.topic_id = p.topic_id
				AND p.post_id = t.topic_first_post_id
				AND u.user_id = p.poster_id
				AND f.forum_id = t.forum_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$post_data = generate_text_for_edit($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield']);
		$row['post_text'] = $post_data['text'];

		return $row;
	}

	/**
	 * Get fields data from post
	 */
	public function get_fields_data_from_post($post_text, $fields)
	{
		$fields_data = array();
		$find_tags = join('|', $fields);

		if (preg_match_all("/\[tag=($find_tags)\](.*?)\[\/tag]/is", $post_text, $matches))
		{
			$fields_data = array_combine($matches[1], $matches[2]);
		}

		return $fields_data;
	}

	/**
	 * Join fields data into a pseudo bbcode for storage as post_text
	 */
	public function generate_message($fields_data)
	{
		$message = '';
		foreach ($fields_data as $field => $value)
		{
			$message .= '[tag=' . $field . ']' . $value . '[/tag]';
		}

		return $message;
	}

	public function set_topic_data($topic_id, $subject, $sql_array)
	{
		$slugify = new Slugify();
		$sql_array['topic_slug'] = $slugify->slugify($subject);

		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . '
			WHERE topic_id = ' . (int) $topic_id;
		$this->db->sql_query($sql);
	}

	public function handle_crud($mode, $u_action)
	{
		$this->user->add_lang_ext('primetime/content', 'manager');

		$topic_id	= $this->request->variable('t', 0);
		$action		= $this->request->variable('action', '');
		$type		= $this->request->variable('type', '');

		$content_forum	= unserialize($this->config['primetime_content_forums']);
		$content_types	= $this->displayer->get_all_types();

		if (!sizeof($content_types))
		{
			return;
		}

		$forum_id	= 0;
		$type_data	= array();

		if ($type && isset($content_types[$type]))
		{
			$type_data	= $content_types[$type];
			$forum_id	= $type_data['forum_id'];
			$user_is_mod = $this->auth->acl_get('m_', $forum_id);
		}
		else
		{
			$user_is_mod = (sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('m_', true)))) ? true : false;
		}

		if ($mode == 'mcp' && $action && !in_array($action, array('edit', 'post', 'view')))
		{
			if ($this->request->is_set('topic_id_list'))
			{
				$mcp_mode = '';
				$topic_ids = $this->request->variable('topic_id_list', array(0));
			}
			else
			{
				$mcp_mode = 'quickmod';
				$topic_ids = array($topic_id);
			}

			$topic_ids = array_filter($topic_ids);

			if (!sizeof($topic_ids))
			{
				trigger_error('NO_TOPIC_SELECTED');
			}

			$sql = 'SELECT forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids) . '
					AND ' . $this->db->sql_in_set('forum_id', array_keys($content_forum));
			$result = $this->db->sql_query($sql);

			$forum_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_ids[] = $row['forum_id'];
			}
			$this->db->sql_freeresult($result);

			$forum_ids = array_flip($forum_ids);

			switch ($action)
			{
				case 'approve':
				case 'disapprove':
					include($this->phpbb_root_path . 'includes/mcp/mcp_queue.' . $this->php_ext);
					include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
				case 'restore_topic':
					$is_authed = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('m_approve', true)))) ? true : false;
				break;
				case 'lock':
				case 'unlock':
					$is_authed = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('m_lock', true)))) ? true : false;
				break;
				case 'resync':
					$is_authed = $user_is_mod;
				break;
				case 'delete_topic':
					$is_authed = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('m_delete', true)))) ? true : false;
				break;
				case 'make_normal':
					$is_authed = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('f_announce', true))) || sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('f_sticky', true)))) ? true : false;
				case 'make_sticky':
					$is_authed = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('f_sticky', true)))) ? true : false;
				break;
				case 'make_announce':
				case 'make_global':
					$is_authed = (sizeof(array_intersect_key($forum_ids, $this->auth->acl_getf('f_announce', true)))) ? true : false;
				break;
			}

			if (!$is_authed)
			{
				trigger_error('NOT_AUTHORISED');
			}

			switch ($action)
			{
				case 'approve':
					\mcp_queue::approve_topics($action, $topic_ids, '-primetime-content-mcp-content_module', 'content');
				break;
				case 'disapprove':
					$sql = 'SELECT post_id
						FROM ' . POSTS_TABLE . '
						WHERE ' . $this->db->sql_in_set('post_visibility', array(ITEM_UNAPPROVED, ITEM_REAPPROVE)) . '
							AND ' . $this->db->sql_in_set('topic_id', $topic_ids);
					$result = $this->db->sql_query($sql);
					$post_id_list = array();
					while ($row = $this->db->sql_fetchrow($result))
					{
						$post_id_list[] = (int) $row['post_id'];
					}
					$this->db->sql_freeresult($result);

					if (!empty($post_id_list))
					{
						\mcp_queue::disapprove_posts($post_id_list, '-primetime-content-mcp-content_module', 'content');
					}
					else
					{
						trigger_error('NO_POST_SELECTED');
					}
				break;
				case 'resync':
					include($this->phpbb_root_path . 'includes/mcp/mcp_forum.' . $this->php_ext);

					mcp_resync_topics($topic_ids);
				break;
				default:
					include($this->phpbb_root_path . 'includes/mcp/mcp_main.' . $this->php_ext);

					$mcp = new \mcp_main($mode);
					$mcp->main("-primetime-content-mcp-content_module", $mcp_mode);
				break;
			}
		}

		switch ($action)
		{
			case 'view':

				$options = array(
					'topic_id'			=> $topic_id,
					'check_visibility'	=> false,
				);

				$this->forum->build_query($options);
				$topic_data = $this->forum->get_topic_data();

				if (!sizeof($topic_data))
				{
					trigger_error($this->user->lang['CONTENT_NO_EXIST']);
				}

				$topic_data = array_shift($topic_data);
				$forum_id = $topic_data['forum_id'];
				$type = $content_forum[$forum_id];
				$type_data = $content_types[$type];

				$row = $this->forum->get_post_data('first');
				$users_cache = $this->forum->get_posters_info();
				$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);

				$topic_id = (int) $topic_data['topic_id'];
				$post_id = (int) $topic_data['topic_first_post_id'];
				$poster_id = (int) $topic_data['topic_poster'];
				$row = array_shift($row[$topic_id]);
				$topic_title = censor_text($topic_data['topic_title']);

				if (($row['post_edit_count'] && $this->config['display_last_edited']) || $row['post_edit_reason'])
				{
					$this->show_edit_reason($row, $user_cache);
				}

				$s_cannot_edit = !$this->auth->acl_get('f_edit', $forum_id) || $this->user->data['user_id'] != $poster_id;
				$s_cannot_edit_time = $this->config['edit_time'] && $row['post_time'] <= time() - ($this->config['edit_time'] * 60);
				$s_cannot_edit_locked = $topic_data['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'];

				$s_cannot_delete = $this->user->data['user_id'] != $poster_id || (
					!$this->auth->acl_get('f_delete', $forum_id) &&
					(!$this->auth->acl_get('f_softdelete', $forum_id) || $row['post_visibility'] == ITEM_DELETED)
				);
				$s_cannot_delete_lastpost = $topic_data['topic_last_post_id'] != $row['post_id'];
				$s_cannot_delete_time = $this->config['delete_time'] && $row['post_time'] <= time() - ($this->config['delete_time'] * 60);

				// we do not want to allow removal of the last post if a moderator locked it!
				$s_cannot_delete_locked = $topic_data['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'];

				$edit_url = '';

				if ($this->user->data['is_registered'] && ($this->auth->acl_get('m_edit', $forum_id) || (
					!$s_cannot_edit &&
					!$s_cannot_edit_time &&
					!$s_cannot_edit_locked)))
				{
					$edit_url = $u_action . "&amp;action=edit&amp;type=$type&amp;t=$topic_id";
				}

				$u_delete_topic = '';
				if ($this->user->data['is_registered'] && (
					($this->auth->acl_get('m_delete', $forum_id) || ($this->auth->acl_get('m_softdelete', $forum_id) && $row['post_visibility'] != ITEM_DELETED)) ||
					(!$s_cannot_delete && !$s_cannot_delete_lastpost && !$s_cannot_delete_time && !$s_cannot_delete_locked)
				))
				{
					if ($mode == 'mcp')
					{
						$u_delete_topic = $u_action . "&amp;action=delete_topic&amp;type=$type&amp;t=$topic_id";
					}
					else
					{
						$u_delete_topic = append_sid("{$this->phpbb_root_path}posting." . $this->php_ext, "mode=delete&amp;f=$forum_id&amp;p=" . $post_id);
					}
				}

				// Deleting information
				if ($row['post_visibility'] == ITEM_DELETED && $row['post_delete_user'])
				{
					// User having deleted the post also being the post author?
					if (!$row['post_delete_user'] || $row['post_delete_user'] == $row['poster_id'])
					{
						$display_username = $users_cache[$row['poster_id']]['author_full'];
					}
					else
					{
						$sql = 'SELECT user_id, username, user_colour
							FROM ' . USERS_TABLE . '
							WHERE user_id = ' . (int) $row['post_delete_user'];
						$result = $this->db->sql_query($sql);
						$user_delete_row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);
						$display_username = get_username_string('full', $row['post_delete_user'], $user_delete_row['username'], $user_delete_row['user_colour']);
					}

					$this->user->add_lang('viewtopic');
					$l_deleted_by = $this->user->lang('DELETED_INFORMATION', $display_username, $this->user->format_date($row['post_delete_time'], false, true));
				}
				else
				{
					$l_deleted_by = '';
				}

				$this->displayer->prepare_to_show($type, 'detail', $type_data['detail_tags'], $type_data['detail_tpl']);

				$tpl_data = array(
					'S_VIEWING'				=> true,
					'S_POST_DELETED'		=> ($row['post_visibility'] == ITEM_DELETED) ? true : false,
					'S_POST_REPORTED'		=> ($row['post_reported'] && $this->auth->acl_get('m_report', $forum_id)),
					'S_POST_UNAPPROVED'		=> (($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $forum_id)),
					'S_POST_DELETED'		=> ($row['post_visibility'] == ITEM_DELETED && $this->auth->acl_get('m_approve', $forum_id)),

					'TOPIC_ID'				=> $topic_id,
					'DELETED_MESSAGE'		=> $l_deleted_by,
					'DELETE_REASON'			=> $row['post_delete_reason'],

					'U_INFO'				=> ($this->auth->acl_get('m_info', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $this->user->session_id) : '',
					'U_MCP_REPORT'			=> ($this->auth->acl_get('m_report', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',
					'U_MCP_APPROVE'			=> ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=unapproved_topics', true, $this->user->session_id) : '',
					'U_MCP_RESTORE'			=> ($this->auth->acl_get('m_approve', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=deleted_topics', true, $this->user->session_id) : '',
					'U_NOTES'				=> ($this->auth->acl_getf_global('m_')) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $this->user->session_id) : '',
					'U_WARN'				=> ($this->auth->acl_get('m_warn') && $poster_id != $this->user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $this->user->session_id) : '',

					'U_REDIRECT'			=> $u_action,
					'U_EDIT'				=> $edit_url,
					'U_DELETE'				=> $u_delete_topic,
				);

				$tpl_data += $this->displayer->show($type, $topic_title, $topic_data, $row, $users_cache[$poster_id], $topic_tracking_info);
				$this->template->assign_vars($tpl_data);

			break;

			case 'edit':
			case 'post':

				$submit = $this->request->is_set_post('submit');
				$preview = $this->request->is_set_post('preview');
				$cancel = $this->request->is_set_post('cancel');

				if (!isset($content_types[$type]))
				{
					trigger_error('INVALID_CONTENT_TYPE');
				}

				if ($cancel)
				{
					redirect($u_action);
				}

				$subject = '';
				$content_fields = $type_data['content_fields'];

				if (!$this->auth->acl_get('f_post', $forum_id))
				{
					trigger_error('NOT_AUTHORISED');
				}

				$post_data = array(
					'forum_id'			=> $forum_id,
					'topic_id'			=> $topic_id,
					'post_id'			=> 0,
					'icon_id'			=> false,

					'post_edit_locked'  => 0,
					'notify_set'        => true,
					'notify'            => true,
					'post_time'         => 0,
					'forum_name'        => '',
					'enable_indexing'   => true,
				);

				if ($topic_id && $action == 'edit')
				{
					$post_data = array_merge($post_data, $this->get_post_data($forum_id, $topic_id));

					$fields_data = $this->get_fields_data_from_post($post_data['post_text'], array_keys($content_fields));
					$subject = $post_data['post_subject'];

					foreach ($fields_data as $field => $value)
					{
						if (isset($content_fields[$field]))
						{
							$content_fields[$field]['field_value'] = $value;
						}
					}
				}
				else
				{
					$post_data += array(
						'topic_time'				=> time(),
						'topic_poster'				=> $this->user->data['user_id'],
						'topic_first_poster_name'	=> $this->user->data['username'],
						'topic_first_poster_colour'	=> $this->user->data['user_colour'],
						'topic_posts_approved'		=> 1,
						'topic_posts_unapproved'	=> 0,
						'topic_posts_softdeleted'	=> 0,
						'topic_posts'				=> 0,
						'topic_last_post_time'		=> time(),
						'topic_slug'				=> '#',
					);
					$post_data = array_merge($post_data, $this->user->data);
				}

				$this->form->create('postform', $u_action . "&action=$action&t=$topic_id", '', 'post', $forum_id)
					->add('subject', 'text', array('field_id' => 'topic-subject', 'field_label' => $this->user->lang['CONTENT_TITLE'], 'field_size' => 65, 'field_value' => $subject, 'field_required' => true));

				$mod_only_required = $mod_only_content = array();
				foreach ($content_fields as $field => $row)
				{
					unset($row['field_id']);

					if (!$row['field_mod_only'] || $user_is_mod)
					{
						$row['field_explain'] = generate_text_for_display($row['field_explain'], $row['field_exp_uid'], $row['field_exp_bitfield'], $row['field_exp_options']);
						$this->form->add($field, $row['field_type'], $row, $topic_id);
					}
					else if (isset($row['field_value']))
					{
						$mod_only_content[$field] = $row['field_value'];
						if ($row['field_required'])
						{
							$mod_only_required[] = $field;
						}
					}
				}

				if ($mode == 'mcp')
				{
					$time = $this->user->create_datetime();
					$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

					$this->form->add('topic_time', 'datetime', array(
						'field_label'		=> $this->user->lang['CONTENT_POST_DATE'],
						'field_value'		=> $this->user->format_date($post_data['topic_time'], 'm/d/Y H:i'),
						'field_min_date'	=> $now['mon'] . '/' . $now['mday'] . '/' . $now['year'],
					));
				}
				else
				{
					if (sizeof($mod_only_required) || (!$user_is_mod && $type_data['req_approval']))
					{
						$post_data['force_visibility'] = (!$topic_id) ? ITEM_UNAPPROVED : ITEM_REAPPROVE;
					}
				}

				$this->form->add('cancel', 'submit', array('field_value' => $this->user->lang['CANCEL'], 'validate_form' => false))
					->add('preview', 'submit', array('field_value' => $this->user->lang['PREVIEW']))
					->add('submit', 'submit', array('field_value' => $this->user->lang['SUBMIT']))
					->add('action', 'hidden', array('field_value' => $action))
					->add('type', 'hidden', array('field_value' => $type));

				$message_fields = $this->form->handle_request($this->request);

				if ($this->form->is_valid)
				{
					// required by message_parser
					global $phpbb_root_path, $phpEx;

					include($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);

					$error = $mod_data = array();
					$allow_bbcode = true;
					$allow_smilies = true;
					$allow_urls = true;

					$subject = utf8_normalize_nfc($this->request->variable('subject', $subject, true));

					if (utf8_clean_string($subject) === '')
					{
						$error[] = $this->user->lang['EMPTY_SUBJECT'];
					}

					// handle moderator actions
					if ($mode == 'mcp')
					{
						$topic_time = $message_fields['topic_time'];

						if ($topic_time && $topic_time !== $post_data['topic_time'])
						{
							$mod_data['topic_time'] = strtotime($topic_time);
						}
					}

					// make sure we only retrieve fields for this content type
					$message_fields = array_filter(array_intersect_key($message_fields, $content_fields));

					// make sure non-mod is not overwriting mod-only fields
					if ($topic_id && $action == 'edit')
					{
						$message_fields = array_merge($message_fields, $mod_only_content);
					}

					$message = $this->generate_message($message_fields);

					include($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);

					$message_parser = new \parse_message($message);

					// Allowing Quote BBCode
					$message_parser->parse($allow_bbcode, $allow_urls, $allow_smilies, true, true, true, true, true, 'post');

					if (sizeof($message_parser->warn_msg))
					{
						$error[] = implode('<br />', $message_parser->warn_msg);
					}

					// Submit
					if ($submit)
					{
						if (!sizeof($error))
						{
							$post_data = array_merge($post_data, array(
								'enable_bbcode'		=> $allow_bbcode,
								'enable_smilies'	=> $allow_smilies,
								'enable_urls'		=> $allow_urls,
								'enable_sig'		=> false,

								'message'			=> (string) $message_parser->message,
								'message_md5'		=> md5($message),
								'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
								'bbcode_uid'		=> (string) $message_parser->bbcode_uid,

								'post_edit_locked'  => 0,
								'topic_title'       => $subject,
							));

							submit_post($action, $subject, $this->user->data['username'], POST_NORMAL, $poll, $post_data);

							if (isset($post_data['topic_id']))
							{
								$this->form->save_fields((int) $post_data['topic_id']);
								$this->set_topic_data($post_data['topic_id'], $subject, $mod_data);
							}

							$message = $this->user->lang['CONTENT_UPDATED'] . '<br /><br />' . sprintf($this->user->lang['RETURN_PAGE'], '<a href="' . $u_action . '">', '</a>');
							trigger_error($message);
							meta_refresh(3, $u_action);
						}

						// Replace "error" strings with their real, localised form
						$error = array_map(array($user, 'lang'), $error);
					}

					// Preview
					if ($preview)
					{
						$this->user->add_lang('viewtopic');
						$this->user->add_lang_ext('primetime/content', 'content');

						if (!function_exists('get_user_rank'))
						{
							include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
						}

						if ($this->phpbb_container->has($type_data['display_type']))
						{
							$view = $this->phpbb_container->get($type_data['display_type']);
						}
						else
						{
							$view = $this->phpbb_container->get('primetime.content.view.portal');
						}

						$post_data['preview'] = true;
						$post_data['post_text'] = $message_parser->format_display($allow_bbcode, $allow_urls, $allow_smilies, false);
						$poster_id = $this->user->data['user_id'];

						$user_data = array(
							'user_type'					=> $this->user->data['user_type'],
							'user_inactive_reason'		=> $this->user->data['user_inactive_reason'],

							'joined'		=> $this->user->format_date($this->user->data['user_regdate']),
							'posts'			=> $this->user->data['user_posts'],
							'warnings'		=> (isset($this->user->data['user_warnings'])) ? $this->user->data['user_warnings'] : 0,

							'viewonline'	=> $this->user->data['user_allow_viewonline'],
							'allow_pm'		=> $this->user->data['user_allow_pm'],

							'avatar'		=> ($this->user->optionget('viewavatars')) ? phpbb_get_user_avatar($this->user->data) : '',
							'age'			=> '',

							'rank_title'		=> '',
							'rank_image'		=> '',
							'rank_image_src'	=> '',

							'username'			=> $this->user->data['username'],
							'user_colour'		=> $this->user->data['user_colour'],
							'contact_user' 		=> $this->user->lang('CONTACT_USER', get_username_string('username', $poster_id, $this->user->data['username'], $this->user->data['user_colour'], $this->user->data['username'])),

							'online'			=> false,
							'jabber'			=> ($this->user->data['user_jabber'] && $this->auth->acl_get('u_sendim')) ? append_sid("{$this->phpbb_root_path}memberlist.$this->php_ext", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '',
							'search'			=> ($this->auth->acl_get('u_search')) ? append_sid("{$this->phpbb_root_path}search.$this->php_ext", "author_id=$poster_id&amp;sr=posts") : '',

							'author_full'		=> get_username_string('full', $poster_id, $this->user->data['username'], $this->user->data['user_colour']),
							'author_colour'		=> get_username_string('colour', $poster_id, $this->user->data['username'], $this->user->data['user_colour']),
							'author_username'	=> get_username_string('username', $poster_id, $this->user->data['username'], $this->user->data['user_colour']),
							'author_profile'	=> get_username_string('profile', $poster_id, $this->user->data['username'], $this->user->data['user_colour']),
						);

						get_user_rank($this->user->data['user_rank'], $this->user->data['user_posts'], $this->user->data['rank_title'], $this->user->data['rank_image'], $this->user->data['rank_image_src']);

						if ((!empty($row['user_allow_viewemail']) && $this->auth->acl_get('u_sendemail')) || $this->auth->acl_get('a_email'))
						{
							$user_data['email'] = ($this->config['board_email_form'] && $this->config['email_enable']) ? append_sid("{$this->phpbb_root_path}memberlist.$this->php_ext", "mode=email&amp;u=$poster_id") : (($this->config['board_hide_emails'] && !$this->auth->acl_get('a_email')) ? '' : 'mailto:' . $this->user->data['user_email']);
						}
						else
						{
							$user_data['email'] = '';
						}

						if ($this->config['allow_birthdays'] && !empty($this->user->data['user_birthday']))
						{
							list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $this->user->data['user_birthday']));

							if ($bday_year)
							{
								$diff = $now['mon'] - $bday_month;
								if ($diff == 0)
								{
									$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
								}
								else
								{
									$diff = ($diff < 0) ? 1 : 0;
								}

								$user_dat['age'] = (int) ($now['year'] - $bday_year - $diff);
							}
						}

						$this->displayer->prepare_to_show($type, 'detail', $type_data['detail_tags'], $type_data['detail_tpl']);
						$this->template->assign_vars($this->displayer->show($type, $subject, $post_data, $post_data, $user_data));

						$this->displayer->prepare_to_show($type, 'summary', $type_data['summary_tags'], $type_data['summary_tpl']);
						$this->template->assign_block_vars('topic_row', $this->displayer->show($type, $subject, $post_data, $post_data, $user_data));

						$this->template->assign_vars(array(
							'S_HIDE_HEADERS'	=> true,
							'S_PREVIEW'			=> true,
							'SUMMARY_TPL'		=> $view->get_index_template(),
							'DETAIL_TPL'		=> $view->get_detail_template(),
						));
					}
					unset($message_parser, $topic_data, $post_data);
				}

				$this->template->assign_vars(array(
					'S_EDITING'		=> true,
					'TYPE_DESC'		=> generate_text_for_display($type_data['content_desc'], $type_data['content_desc_uid'], $type_data['content_desc_bitfield'], $type_data['content_desc_options']),
					'POST_FORM'		=> $this->form->get_form()
				));

			break;

			default:

				include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);

				$this->user->add_lang('viewforum');

				$start = $this->request->variable('start', 0);
				$filter_content_type = $this->request->variable('type', '');
				$filter_topic_status = $this->request->variable('status', '');
				$filter_topic_search = utf8_normalize_nfc($this->request->variable('search', '', true));

				$time = time();
				$sql_where_array = array();
				$filter_topic_status_ary 	= array(
					'-1'				=> 'scheduled',
					ITEM_UNAPPROVED		=> 'unapproved',
					ITEM_APPROVED		=> 'published',
					ITEM_DELETED		=> 'deleted',
				);
				$filter_topic_types_ary		= array(
					POST_NORMAL		=> 'published',
					POST_STICKY		=> 'featured',
					POST_ANNOUNCE	=> 'recommended',
					POST_GLOBAL		=> 'must_read',
				);

				if ($mode == 'mcp')
				{
					$s_can_approve = (sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('m_approve', true)))) ? true : false;
					$s_can_make_sticky = (sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('f_sticky', true)))) ? true : false;
					$s_can_make_announce = (sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('f_announce', true)))) ? true : false;

					$this->template->assign_vars(array(
						'S_CAN_DELETE'			=> (sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('m_delete', true)))) ? true : false,
						'S_CAN_RESTORE'			=> $s_can_approve,
						'S_CAN_LOCK'			=> (sizeof(array_intersect_key($content_forum, $this->auth->acl_getf('m_lock', true)))) ? true : false,
						'S_CAN_SYNC'			=> $user_is_mod,
						'S_CAN_APPROVE'			=> $s_can_approve,
						'S_CAN_MAKE_NORMAL'		=> ($s_can_make_sticky || $s_can_make_announce),
						'S_CAN_MAKE_STICKY'		=> $s_can_make_sticky,
						'S_CAN_MAKE_ANNOUNCE'	=> $s_can_make_announce,
					));
				}
				else
				{
					$sql_where_array[] =  't.topic_poster = ' . (int) $this->user->data['user_id'];

					// list all content types that the customer can post in
					$postable_forums = array_intersect_key($content_forum, $this->auth->acl_getf('f_post', true));
					foreach ($postable_forums as $forum_id => $content_type)
					{
						$this->template->assign_block_vars('postable', array(
							'TYPE'		=> $content_types[$content_type]['content_langname'],
							'COLOUR'	=> $content_types[$content_type]['content_colour'],
							'U_POST'	=> $u_action . '&amp;action=post&amp;type=' . $content_type
						));
					}
				}

				// filter by content type
				if ($filter_content_type && in_array($filter_content_type, $content_forum))
				{
					$content_forum = array_intersect($content_forum, array($filter_content_type));
					$this->template->assign_var('S_CONTENT_FILTER', true);
				}

				// filter by topic status
				if ($filter_topic_status)
				{
					$this->template->assign_var('S_STATUS_FILTER', true);

					switch ($filter_topic_status)
					{
						case 'scheduled':
							$sql_where_array[] = 't.topic_time > ' . $time;
						break;
						case 'unapproved':
						case 'published':
						case 'deleted':
							$sql_where_array[] = 't.topic_visibility = ' . array_search($filter_topic_status, $filter_topic_status_ary);
						break;
						case 'recommended':
						case 'featured':
						case 'must_read':
							$sql_where_array[] = 't.topic_type = ' . array_search($filter_topic_status, $filter_topic_types_ary);
							$sql_where_array[] = 't.topic_visibility = ' . ITEM_APPROVED;
						break;
					}
				}

				if ($filter_topic_search)
				{
					$sql_where_array[] = 't.topic_title ' . $this->db->sql_like_expression($this->db->get_any_char() . $filter_topic_search . $this->db->get_any_char());
				}

				$sql_where_array[] = $this->db->sql_in_set('t.forum_id', array_keys($content_forum));

				$sql_array['WHERE'] = join(' AND ', $sql_where_array);

				$params = array_filter(array(
					'type'		=> $filter_content_type,
					'status'	=> $filter_topic_status,
					'search'	=> $filter_topic_search,
				));

				$options = array(
					'sort_key'			=> 't.topic_time',
					'topic_tracking'	=> true,
					'check_visibility'	=> false,
				);

				// Get topics count
				$sql = 'SELECT COUNT(*) as topics_count
					FROM ' . TOPICS_TABLE . ' t
					WHERE ' . $sql_array['WHERE'];
				$result = $this->db->sql_query($sql);

				$topics_count = $this->db->sql_fetchfield('topics_count');
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'TOTAL_TOPICS'		=> $this->user->lang('VIEW_FORUM_TOPICS', (int) $topics_count),
					'S_ACTION'			=> $u_action,
				));

				// grab the topics
				$this->forum->build_query($options, $sql_array);
				$topic_data = $this->forum->get_topic_data($this->config['topics_per_page'], $start);
				$topic_tracking_info = $this->forum->get_topic_tracking_info();

				$topic_data = array_values($topic_data);

				// Grab icons
				$icons = $this->cache->obtain_icons();

				$base_url = $u_action . ((sizeof($params)) ? '&amp;' : '') . http_build_query($params);

				$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $topics_count);
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $topics_count, $this->config['topics_per_page'], $start);

				for ($i = 0, $size = sizeof($topic_data); $i < $size; $i++)
				{
					$row = $topic_data[$i];
					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];
					$content_type = $content_forum[$forum_id];
					$type_data = &$content_types[$content_type];

					if ($row['topic_status'] == ITEM_MOVED)
					{
						$unread_topic = false;
					}
					else
					{
						$unread_topic = (isset($topic_tracking_info[$forum_id][$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$topic_id]) ? true : false;
					}

					$topic_title = censor_text($row['topic_title']);
					$topic_unapproved = ($row['topic_visibility'] == ITEM_UNAPPROVED || $row['topic_visibility'] == ITEM_REAPPROVE) ? true : false;
					$posts_unapproved = ($row['topic_visibility'] == ITEM_APPROVED && $row['topic_posts_unapproved'] && $this->auth->acl_get('m_approve', $forum_id)) ? true : false;
					$topic_deleted = $row['topic_visibility'] == ITEM_DELETED;
					$view_type[$type_data['content_name']] = $type_data['content_langname'];

					$topic_status = '';
					$num_comments = 0;
					$allow_comments = false;

					if ($type_data['allow_comments'])
					{
						$allow_comments = true;
						$num_comments = $this->comments->count($row);
					}

					// Get folder img, topic status/type related information
					$folder_img = $folder_alt = $topic_type = '';
					topic_status($row, $num_comments, $unread_topic, $folder_img, $folder_alt, $topic_type);

					if ($topic_deleted)
					{
						$topic_status = 'deleted';
					}
					else if ($topic_unapproved)
					{
						$topic_status = 'unapproved';
					}
					else if ($row['topic_time'] > $time)
					{
						$topic_status = 'scheduled';
					}
					else
					{
						$topic_status = $filter_topic_types_ary[$row['topic_type']];
					}

					$u_mcp_queue = '';
					if ($mode == 'mcp')
					{
						$url = append_sid("{$this->phpbb_root_path}mcp." . $this->php_ext, "f=$forum_id");
						$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? $url . '&amp;i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . '&amp;t=' . $row['topic_id'] : '';
						$u_mcp_queue = (!$u_mcp_queue && $topic_deleted) ? $url . '&amp;i=queue&amp;mode=deleted_topics&amp;t=' . $topic_id : $u_mcp_queue;
						$u_delete_topic = $u_action . "&amp;action=delete_topic&amp;type=$content_type&amp;t=$topic_id";
					}
					else
					{
						$u_delete_topic = append_sid("{$this->phpbb_root_path}posting." . $this->php_ext, "mode=delete&amp;f=$forum_id&amp;p=" . $row['topic_first_post_id']);
					}

					$u_topic_review = $u_action . '&amp;action=view&amp;t=' . $topic_id;
					$u_viewtopic = $this->helper->route('primetime_content_show', array(
						'type'		=> $content_type,
						'topic_id'	=> $topic_id,
						'slug'		=> $row['topic_slug']
					));

					$topic_row = array(
						'ATTACH_ICON_IMG'			=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->user->lang['TOTAL_ATTACHMENTS']) : '',
						'TOPIC_IMG_STYLE'			=> $folder_img,
						'TOPIC_FOLDER_IMG'			=> $this->user->img($folder_img, $folder_alt),
						'TOPIC_ICON_IMG'			=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
						'TOPIC_ICON_IMG_WIDTH'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
						'TOPIC_ICON_IMG_HEIGHT'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
						'UNAPPROVED_IMG'			=> ($topic_unapproved || $posts_unapproved) ? $this->user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',
						'DELETED_IMG'				=> ($topic_deleted) ? $this->user->img('icon_topic_deleted', 'POSTS_DELETED') : '',

						'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'U_TOPIC_AUTHOR'			=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),

						'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'U_LAST_POST_AUTHOR'		=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

						'CONTENT_TYPE'			=> $type_data['content_langname'],
						'CONTENT_TYPE_COLOR'	=> '#' . $type_data['content_colour'],
						'TOPIC_VIEWS'			=> $row['topic_views'],
						'TOPIC_ID'				=> $topic_id,
						'TOPIC_TYPE'			=> $topic_type,
						'TOPIC_TITLE'			=> $topic_title,
						'TOPIC_STATUS'			=> $this->user->lang['TOPIC_' . strtoupper($topic_status)],
						'TOPIC_COMMENTS'		=> $num_comments,
						'LAST_POST_TIME'		=> $this->user->format_date($row['topic_last_post_time']),
						'FIRST_POST_TIME'		=> $this->user->format_date($row['topic_time']),
						'LAST_POST_SUBJECT'		=> $row['topic_last_post_subject'],
						'LAST_VIEW_TIME'		=> $this->user->format_date($row['topic_last_view_time']),

						'S_COMMENTS'			=> $allow_comments,
						'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && empty($row['topic_moved_id']) && $auth->acl_get('m_report', $forum_id)) ? true : false,
						'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
						'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
						'S_TOPIC_DELETED'		=> $topic_deleted,
						'S_UNREAD_TOPIC'		=> $unread_topic,
						'S_CAN_EDIT'			=> true,
						'S_CAN_DELETE'			=> true,

						'U_VIEW_TOPIC'			=> $u_viewtopic,
						'U_REVIEW_TOPIC'		=> $u_topic_review,
						'U_EDIT_TOPIC'			=> $u_action . "&amp;action=edit&amp;type=$content_type&amp;t=$topic_id",
						'U_DELETE_TOPIC'		=> $u_delete_topic,
						'U_CONTENT_TYPE'		=> $base_url . "&amp;type=$content_type",
						'U_TOPIC_STATUS'		=> $base_url . "&amp;status=$topic_status",
						'U_MCP_QUEUE'			=> $u_mcp_queue,
					);

					$this->template->assign_block_vars('topicrow', $topic_row);
					unset($topic_data[$i]);
				}

				$u_action .= (sizeof($params)) ? '&amp;' : '';

				// generate content type filters
				$copy_params = $params;
				unset($copy_params['type']);
				$base_url = $u_action . http_build_query($copy_params);
				$this->generate_content_type_filter($filter_content_type, $content_types, $base_url);

				// generate status filters
				$copy_params = $params;
				unset($copy_params['status']);
				$base_url = $u_action . http_build_query($copy_params);
				$this->generate_topic_status_filter($filter_topic_status, array_merge($filter_topic_status_ary, $filter_topic_types_ary), $base_url);

			break;
		}
	}

	public function generate_content_type_filter($type, $content_types, $view_url)
	{
		$this->template->assign_block_vars('content', array(
			'TITLE'			=> $this->user->lang['TOPIC_ALL'],
			'COLOUR'		=> '',
			'S_SELECTED'	=> (!$type) ? true : false,
			'U_VIEW'		=> $view_url
		));

		foreach ($content_types as $row)
		{
			$this->template->assign_block_vars('content', array(
				'TITLE'			=> $row['content_langname'],
				'COLOUR'		=> $row['content_colour'],
				'S_SELECTED'	=> ($type == $row['content_name']) ? true : false,
				'U_VIEW'		=> $view_url . '&amp;type=' . $row['content_name']
			));
		}
		$this->db->sql_freeresult();
	}

	public function generate_topic_status_filter($topic_status, $topic_status_ary, $view_url)
	{
		$this->template->assign_block_vars('status', array(
			'TITLE'			=> $this->user->lang['TOPIC_ALL'],
			'S_SELECTED'	=> (!$topic_status) ? true : false,
			'U_VIEW'		=> $view_url
		));

		$topic_status_ary = array_unique($topic_status_ary);
		foreach ($topic_status_ary as $status)
		{
			$this->template->assign_block_vars('status', array(
				'TITLE'			=> $this->user->lang['TOPIC_' . strtoupper($status)],
				'S_SELECTED'	=> ($status == $topic_status) ? true : false,
				'U_VIEW'		=> $view_url . '&amp;status=' . $status
			));
		}
	}
}
