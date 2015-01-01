<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\controller;

use Symfony\Component\DependencyInjection\Container;

class display
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var phpbb\pagination */
	protected $pagination;

	/** @var Container */
	protected $phpbb_container;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/* @var \primetime\content\services\displayer */
	protected $displayer;

	/** @var \primetime\primetime\core\forum\query */
	protected $forum;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\config\db							$config				Config object
	 * @param \phpbb\controller\helper					$helper				Helper object
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param Container									$phpbb_container	Service container
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\user								$user				User object
	 * @param \primetime\content\services\displayer		$displayer			Content displayer object
	 * @param \primetime\primetime\core\forum\query		$forum				Forum object
	 * @param string									$root_path			Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, Container $phpbb_container, \phpbb\template\template $template, \phpbb\user $user, \primetime\content\services\displayer $displayer, \primetime\primetime\core\forum\query $forum, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->phpbb_container = $phpbb_container;
		$this->template = $template;
		$this->user = $user;
		$this->displayer = $displayer;
		$this->forum = $forum;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function index($type, $page = 1, $filter = '', $filter_value = '')
	{
		$type_data = $this->displayer->get_type($type);

		if (!sizeof($type_data) || !$type_data['content_enabled'])
		{
			return $this->helper->error($this->user->lang['INVALID_CONTENT_TYPE']);
		}

		if ($this->phpbb_container->has($type_data['display_type']))
		{
			$view = $this->phpbb_container->get($type_data['display_type']);
		}
		else
		{
			$view = $this->phpbb_container->get('primetime.content.view.portal');
		}

		$forum_id = (int) $type_data['forum_id'];
		$content_langname = $type_data['content_langname'];

		if ($type_data['index_show_desc'])
		{
			$this->template->assign_vars(array(
				'CONTENT_TYPE'		=> $content_langname,
				'CONTENT_DESC'		=> generate_text_for_display($type_data['content_desc'], $type_data['content_desc_uid'], $type_data['content_desc_bitfield'], $type_data['content_desc_options'])
			));
		}

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $type_data['content_langname'],
			'U_VIEW_FORUM'	=> $this->helper->route('primetime_content_index', array('type' => $type))
		));

		$start = ($page - 1) * $type_data['items_per_page'];
		$limit = $type_data['items_per_page'];

		$sql_array = array();

		$options = array(
			'forum_id'	=> $forum_id
		);

		$sql_topics_count = $this->forum->build_query($options);
		$sql_topics_count['SELECT'] = 'COUNT(t.topic_id) as total_topics';

		$view->customize_view($sql_topics_count, $sql_array, $type_data, $limit);

		if ($type_data['show_pagination'])
		{
			$total_topics = $view->get_total_topics($forum_id, $sql_topics_count);

			$start = $this->pagination->validate_start($start, $type_data['items_per_page'], $total_topics);
			$this->pagination->generate_template_pagination(
				array(
					'routes' => array(
						'primetime_content_index',
						'primetime_content_index_page',
					),
					'params' => array(
						'type'	=> $type
					),
				),
				'pagination', 'page', $total_topics, $type_data['items_per_page'], $start);
		}

		$this->displayer->prepare_to_show($type, 'summary', $type_data['summary_tags'], $type_data['summary_tpl']);

		$options = array(
			'forum_id'			=> $forum_id,
			'sort_key'			=> 't.topic_time',
			'topic_tracking'	=> true,
		);

		$sql_ary = $this->forum->build_query($options, $sql_array);

		$topics_data = $this->forum->get_topic_data($limit, $start);
		$posts_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
		$users_cache = $this->forum->get_posters_info();

		$view->display_topics($type, $topics_data, $posts_data, $users_cache, $topic_tracking_info);
		unset($type_data, $topic_data, $post_data, $user_cache, $topic_tracking_info, $tpl_data);

		return $this->helper->render($view->get_index_template(), $content_langname);
	}

	public function show($type, $topic_id, $slug, $page = 1)
	{
		$this->user->add_lang('viewtopic');
		$this->user->add_lang_ext('primetime/content', 'content');

		$type_data = $this->displayer->get_type($type);

		if (!sizeof($type_data))
		{
			return $this->helper->error($this->user->lang['INVALID_CONTENT_TYPE']);
		}

		if ($this->phpbb_container->has('primetime.content.view.tiles'))
		{
			$view = $this->phpbb_container->get('primetime.content.view.tiles');
		}
		else
		{
			$view = $this->phpbb_container->get('primetime.content.view.portal');
		}

		$forum_id = (int) $type_data['forum_id'];

		$options = array(
			'forum_id'			=> $forum_id,
			'topic_id'			=> $topic_id,
			'topic_tracking'	=> true,
		);

		$sql_array = array();

		$this->forum->build_query($options);
		$topic_data = $this->forum->get_topic_data();

		if (!sizeof($topic_data))
		{
			return $this->helper->error($this->user->lang['CONTENT_NO_EXIST']);
		}

		$this->displayer->prepare_to_show($type, 'detail', $type_data['detail_tags'], $type_data['detail_tpl']);

		$post_data = $this->forum->get_post_data('first');
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);
		$users_cache = $this->forum->get_posters_info();

		$topic_data = array_shift($topic_data);
		$topic_id = (int) $topic_data['topic_id'];
		$post_id = (int) $topic_data['topic_first_post_id'];
		$poster_id = (int) $topic_data['topic_poster'];
		$post_data = array_shift($post_data[$topic_id]);
		$topic_title = censor_text($topic_data['topic_title']);

		$view->show_topic($topic_title, $type, $topic_data, $post_data, $users_cache[$poster_id], $topic_tracking_info, $page);

		if ($type_data['allow_comments'])
		{
			$this->template->assign_var('S_COMMENTS', true);

			$comments = $this->phpbb_container->get('primetime.content.comments');
			$comments->show($type, $topic_data, $page);
		}

		if ($type_data['show_poster_info'])
		{
			$this->get_author_info($forum_id, $poster_id, $users_cache[$poster_id], $post_id, $type_data['content_langname']);
		}

		if ($type_data['show_poster_contents'])
		{
			$this->get_author_contents($topic_data, $type, $type_data['content_langname']);
		}

		$navlinks = array(
			array(
				'FORUM_NAME'	=> $type_data['content_langname'],
				'U_VIEW_FORUM'	=> $this->helper->route('primetime_content_index', array('type' => $type))
			),
			array(
				'FORUM_NAME'	=> $topic_title,
				'U_VIEW_FORUM'	=> $this->helper->route('primetime_content_show', array(
					'type'			=> $type,
					'topic_id'		=> $topic_id,
					'slug'			=> $topic_data['topic_slug']
				))
			)
		);

		foreach ($navlinks as $item)
		{
			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $item['FORUM_NAME'],
				'U_VIEW_FORUM'	=> $item['U_VIEW_FORUM'],
			));
		}
		unset($type_data, $topic_data, $post_data, $users_cache, $topic_tracking_info, $tpl_data);

		return $this->helper->render($view->get_detail_template(), $topic_title);
	}

	protected function get_author_info($forum_id, $poster_id, $user_cache, $post_id, $content_langname)
	{
		if (!sizeof($user_cache))
		{
			return array();
		}

		include($this->root_path . 'includes/functions_user.' . $this->php_ext);

		$sql = 'SELECT count(*) as user_contents
			FROM ' . TOPICS_TABLE . "
			WHERE topic_poster = $poster_id
				AND forum_id = $forum_id
				AND " . time() . ' > topic_time';
		$result = $this->db->sql_query($sql);
		$user_contents = $this->db->sql_fetchfield('user_contents');
		$this->db->sql_freeresult($result);

		$user_since	= $user_cache['joined'];
		$user_posts	= $user_cache['posts'];
		$username = get_username_string('full', $poster_id, $user_cache['username'], $user_cache['user_colour']);

		$lang = 'NO_GENDER';
		if (isset($user_cache['user_gender']))
		{
			$lang_array = array(GENDER_MALE => 'MALE', GENDER_FEMALE => 'FEMALE');
			$lang = (isset($lang_array[$user_cache['user_gender']])) ? $lang_array[$user_cache['user_gender']] : 'NO_GENDER';
		}
		$user_gender = $this->user->lang['PRON_' . $lang];

		$this->template->assign_vars(array(
			'S_USER_INFO'			=> true,
			'L_USER_ABOUT'			=> sprintf($this->user->lang['AUTHOR_INFO_EXPLAIN'], $username, $user_since, $user_gender, $user_contents, $content_langname, $user_posts),
			'L_USER_VIEW_ALL'		=> sprintf($this->user->lang['VIEW_AUTHOR_CONTENTS'], $content_langname, $username),
			'L_SEARCH_USER_POSTS'	=> sprintf($this->user->lang['SEARCH_USER_POSTS'], $username),
		));

		$permanently_banned_users = phpbb_get_banned_user_ids(array($poster_id), false);

		// Can this user receive a Private Message?
		$can_receive_pm = (
			// They must be a "normal" user
			$user_cache['user_type'] != USER_IGNORE &&

			// They must not be deactivated by the administrator
			($user_cache['user_type'] != USER_INACTIVE || $user_cache['user_inactive_reason'] != INACTIVE_MANUAL) &&

			// They must be able to read PMs
			$this->auth->acl_get_list($poster_id, 'u_readpm') &&

			// They must not be permanently banned
			!sizeof($permanently_banned_users) &&

			// They must allow users to contact via PM
			(($this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_')) || $user_cache['allow_pm'])
		);

		$u_pm = '';
		if ($this->config['allow_privmsg'] && $this->auth->acl_get('u_sendpm') && $can_receive_pm)
		{
			$u_pm = append_sid("{$this->root_path}ucp.$this->php_ext", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $post_id);
		}

		$contact_fields = array(
			array(
				'ID'		=> 'pm',
				'NAME' 		=> $this->user->lang['SEND_PRIVATE_MESSAGE'],
				'U_CONTACT'	=> $u_pm,
			),
			array(
				'ID'		=> 'email',
				'NAME'		=> $this->user->lang['SEND_EMAIL'],
				'U_CONTACT'	=> $user_cache['email'],
			),
			array(
				'ID'		=> 'jabber',
				'NAME'		=> $this->user->lang['JABBER'],
				'U_CONTACT'	=> $user_cache['jabber'],
			),
		);

		foreach ($contact_fields as $field)
		{
			if ($field['U_CONTACT'])
			{
				$this->template->assign_block_vars('contact', $field);
			}
		}

	}

	protected function get_author_contents($topic_data, $content_type, $content_langname)
	{
		$user_id = (int) $topic_data['topic_poster'];
		$forum_id = (int) $topic_data['forum_id'];
		$topic_id = (int) $topic_data['topic_id'];
		$username = $topic_data['topic_first_poster_name'];

		$options = array(
			'forum_id'			=> $forum_id,
			'topic_tracking'	=> true,
		);

		$sql_array = array(
			'WHERE'		=> "t.topic_poster = $user_id
				AND t.topic_id <> $topic_id
				AND " . time() . ' > t.topic_time',
		);

		$this->forum->build_query($options, $sql_array);
		$topic_data = $this->forum->get_topic_data(5);
		$topic_tracking_info = $this->forum->get_topic_tracking_info($forum_id);

		if (sizeof($topic_data))
		{
			$this->template->assign_var('AUTHOR_CONTENT', sprintf($this->user->lang['AUTHOR_CONTENTS'], $content_langname, $username));

			foreach ($topic_data as $row)
			{
				$post_unread = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
				$topic_url = $this->helper->route('primetime_content_show', array(
					'type'		=> $content_type,
					'topic_id'	=> $row['topic_id'],
					'slug'		=> $row['topic_slug']
				));

				$this->template->assign_block_vars('content', array(
					'MINI_POST_IMG'	=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),
					'TOPIC_TITLE'	=> censor_text($row['topic_title']),
					'TOPIC_URL'		=> $topic_url
				));
			}
		}
	}
}
