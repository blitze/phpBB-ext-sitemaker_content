<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\comments;

class comments extends form implements comments_interface
{
	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\context */
	protected $template_context;

	/** @var \blitze\sitemaker\services\forum\data */
	protected $forum;

	/** @var \blitze\content\services\topic */
	protected $topic;

	/** @var array */
	private $sort_dir_sql = array('a' => 'ASC', 'd' => 'DESC');

	/** @var array */
	private $sort_by_sql = array(
		't' => 'p.post_time',
		's' => 'p.post_subject, p.post_id',
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\config\config						$config				Config object
	 * @param \phpbb\content_visibility					$content_visibility	Phpbb Content visibility object
	 * @param \phpbb\db\driver\driver_interface			$db					Database object
	 * @param \phpbb\language\language					$language			Language Object
	 * @param \phpbb\pagination							$pagination			Pagination object
	 * @param \phpbb\request\request_interface			$request			Request object
	 * @param \phpbb\template\template					$template			Template object
	 * @param \phpbb\template\context					$template_context	Template context object
	 * @param \phpbb\user								$user				User object
	 * @param \blitze\sitemaker\services\forum\data		$forum				Forum Data object
	 * @param \blitze\content\services\topic			$topic				Topic object
	 * @param string									$root_path			Path to the phpbb directory.
	 * @param string									$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\template\context $template_context, \phpbb\user $user, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\topic $topic, $root_path, $php_ext)
	{
		parent::__construct($auth, $config, $language, $template, $user, $root_path, $php_ext);

		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template_context = $template_context;
		$this->forum = $forum;
		$this->topic = $topic;
	}

	/**
	 * @inheritdoc
	 */
	public function count(array $topic_data)
	{
		return $this->content_visibility->get_count('topic_posts', $topic_data, $topic_data['forum_id']) - 1;
	}

	/**
	 * @inheritdoc
	 */
	public function show_comments($content_type, array $topic_data, array &$update_count)
	{
		if ($topic_data['total_comments'])
		{
			$view		= $this->request->variable('view', '');
			$start		= $this->request->variable('start', 0);
			$post_id	= $this->request->variable('p', 0);

			$this->find_unread($view, $topic_data);

			$sort_days = 0;
			$sort_key = $sort_dir = $u_sort_param = '';
			$this->set_sorting_options($sort_days, $sort_key, $sort_dir, $u_sort_param);

			$base_url = append_sid(trim(build_url(array('start', 'p')), '?'), (strlen($u_sort_param)) ? $u_sort_param : '');
			$this->build_pagination($start, $post_id, $topic_data, $sort_dir, $base_url);

			$this->forum->query()
				->fetch_date_range(time(), $sort_days * 86400, 'post')
				->build();
			$posts_data = $this->forum->get_post_data(false, array(), (int) $this->config['posts_per_page'], $start, array(
				'WHERE'		=> array(
					'p.topic_id = ' . (int) $topic_data['topic_id'],
					'p.post_id <> ' . (int) $topic_data['topic_first_post_id'],
				),
				'ORDER_BY'	=> $this->sort_by_sql[$sort_key] . ' ' . $this->sort_dir_sql[$sort_dir],
			));

			$topic_tracking_info = $this->forum->get_topic_tracking_info();
			$users_cache = $this->forum->get_posters_info();

			$this->show_posts($topic_data, array_values(array_shift($posts_data)), $topic_tracking_info, $users_cache, $update_count, $content_type, $start);
		}
	}

	/**
	 * @param array $topic_data
	 * @param array $posts_data
	 * @param array $topic_tracking_info
	 * @param array $users_cache
	 * @param array $update_count
	 * @param string $type
	 * @param int $start
	 * @return void
	 */
	protected function show_posts(array $topic_data, array $posts_data, array $topic_tracking_info, array $users_cache, array &$update_count, $type, $start)
	{
		$attachments = $this->forum->get_attachments($topic_data['forum_id']);
		$this->set_form_action($topic_data['topic_url'], $start);

		for ($i = 0, $size = sizeof($posts_data); $i < $size; $i++)
		{
			$row = $posts_data[$i];
			$poster_id = $row['poster_id'];

			$this->template->assign_block_vars('postrow', array_merge(
				$this->topic->get_detail_template_data($type, $topic_data, $row, $users_cache, $attachments, $topic_tracking_info, $update_count),
				$this->get_attachments_tpl_data($row['post_id'], $attachments),
				array(
					'POST_SUBJECT'				=> $row['post_subject'],
					'POST_DATE'					=> $this->user->format_date($row['post_time'], false, false),
					'POSTER_WARNINGS'			=> $this->get_poster_warnings($users_cache[$poster_id]),
					'S_POST_REPORTED'			=> $this->get_report_status($row),
					'S_TOPIC_POSTER'			=> ($topic_data['topic_poster'] == $poster_id) ? true : false,
				)
			));

			$this->topic->show_attachments($attachments, $row['post_id'], 'postrow.attachment');
		}
	}

	/**
	 * @param string $view
	 * @param array $topic_data
	 * @retrun void
	 */
	protected function find_unread($view, array $topic_data)
	{
		if ($view === 'unread')
		{
			$forum_id = (int) $topic_data['forum_id'];
			$topic_id = (int) $topic_data['topic_id'];

			// Get topic tracking info
			$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_id);
			$topic_last_read = (isset($topic_tracking_info[$topic_id])) ? $topic_tracking_info[$topic_id] : 0;

			$sql = 'SELECT post_id, topic_id, forum_id
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $topic_id
					AND " . $this->content_visibility->get_visibility_sql('post', $forum_id) . "
					AND post_time > $topic_last_read
					AND forum_id = $forum_id
				ORDER BY post_time ASC, post_id ASC";
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				redirect(append_sid($topic_data['topic_url'], 'p=' . $row['post_id']) . '#p' . $row['post_id']);
			}
		}
	}

	/**
	 * This is for determining where we are (page)
	 * @param int $start
	 * @param int $post_id
	 * @param array $topic_data
	 * @param string $sort_dir
	 * @param string $base_url
	 * @return void
	 */
	protected function build_pagination(&$start, $post_id, array $topic_data, $sort_dir, $base_url)
	{
		if ($post_id)
		{
			$post_info = $this->get_post_info($post_id);
			$this->check_requested_post_id($post_info, $topic_data, $base_url);

			$prev_posts = $this->get_next_posts_count($post_info, $topic_data, $sort_dir, $post_id);
			$start = (int) floor($prev_posts / $this->config['posts_per_page']) * $this->config['posts_per_page'];
		}

		$start = $this->pagination->validate_start($start, (int) $this->config['posts_per_page'], $topic_data['total_comments']);
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $topic_data['total_comments'], (int) $this->config['posts_per_page'], $start);

		$data =& $this->template_context->get_data_ref()['pagination'];
		foreach ($data as &$row)
		{
			$row['PAGE_URL'] .= '#comments';
		}
	}

	/**
	 * @param array $post_info
	 * @param array $topic_data
	 * @param string $base_url
	 * @return void
	 */
	protected function check_requested_post_id(array $post_info, array $topic_data, $base_url)
	{
		// are we where we are supposed to be?
		if (($post_info['post_visibility'] == ITEM_UNAPPROVED || $post_info['post_visibility'] == ITEM_REAPPROVE) && !$this->auth->acl_get('m_approve', $topic_data['forum_id']))
		{
			// If post_id was submitted, we try at least to display the topic as a last resort...
			if ($topic_data['topic_id'])
			{
				redirect($base_url);
			}

			trigger_error('NO_TOPIC');
		}
	}

	/**
	 * @param array $post_info
	 * @param array $topic_data
	 * @param string $sort_dir
	 * @param int $post_id
	 * @return int
	 */
	protected function get_next_posts_count(array $post_info, array $topic_data, $sort_dir, $post_id)
	{
		if ($post_id == $topic_data['topic_first_post_id'] || $post_id == $topic_data['topic_last_post_id'])
		{
			$check_sort = ($post_id == $topic_data['topic_first_post_id']) ? 'd' : 'a';

			$prev_posts_count = 0;
			if ($sort_dir == $check_sort)
			{
				$prev_posts_count = $this->content_visibility->get_count('topic_posts', $topic_data, $topic_data['forum_id']) - 1;
			}
			return $prev_posts_count;
		}
		else
		{
			return $this->get_prev_posts_count($post_info, $topic_data['forum_id'], $topic_data['topic_id'], $sort_dir) - 1;
		}
	}

	/**
	 * @param array $row
	 * @param int $forum_id
	 * @param int $topic_id
	 * @param string $sort_dir
	 * @return int
	 */
	protected function get_prev_posts_count(array $row, $forum_id, $topic_id, $sort_dir)
	{
		$sql = 'SELECT COUNT(p.post_id) AS prev_posts
			FROM ' . POSTS_TABLE . " p
			WHERE p.topic_id = $topic_id
				AND " . $this->content_visibility->get_visibility_sql('post', $forum_id, 'p.');

		if ($sort_dir == 'd')
		{
			$sql .= " AND (p.post_time > {$row['post_time']} OR (p.post_time = {$row['post_time']} AND p.post_id >= {$row['post_id']}))";
		}
		else
		{
			$sql .= " AND (p.post_time < {$row['post_time']} OR (p.post_time = {$row['post_time']} AND p.post_id <= {$row['post_id']}))";
		}

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row['prev_posts'];
	}

	/**
	 * @param int $post_id
	 * @return array
	 */
	protected function get_post_info($post_id)
	{
		$sql = 'SELECT post_id, post_time, post_visibility
			FROM ' . POSTS_TABLE . ' p
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * @param int $sort_days
	 * @param string $sort_key
	 * @param string $sort_dir
	 * @param string $u_sort_param
	 * @return void
	 */
	protected function set_sorting_options(&$sort_days, &$sort_key, &$sort_dir, &$u_sort_param)
	{
		$default_sort_days	= (!empty($this->user->data['user_post_show_days'])) ? $this->user->data['user_post_show_days'] : 0;
		$default_sort_key	= (!empty($this->user->data['user_post_sortby_type'])) ? $this->user->data['user_post_sortby_type'] : 't';
		$default_sort_dir	= (!empty($this->user->data['user_post_sortby_dir'])) ? $this->user->data['user_post_sortby_dir'] : 'a';

		$sort_days	= $this->request->variable('st', $default_sort_days);
		$sort_key	= $this->request->variable('sk', $default_sort_key);
		$sort_dir	= $this->request->variable('sd', $default_sort_dir);

		$limit_days = array(0 => $this->language->lang('ALL_POSTS'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR'));
		$sort_by_text = array('t' => $this->language->lang('POST_TIME'), 's' => $this->language->lang('SUBJECT'));

		$s_limit_days = $s_sort_key = $s_sort_dir = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

		$this->template->assign_vars(array(
			'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
			'S_SELECT_SORT_KEY' 	=> $s_sort_key,
			'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
		));
	}

	/**
	 * @param int $post_id
	 * @param array $attachments
	 * @return array
	 */
	protected function get_attachments_tpl_data($post_id, array $attachments)
	{
		$has_attachments = $multi_attachments = false;
		if (!empty($attachments[$post_id]))
		{
			$has_attachments = true;
			$multi_attachments = sizeof($attachments[$post_id]) > 1;
		}

		return array(
			'S_HAS_ATTACHMENTS'			=> $has_attachments,
			'S_MULTIPLE_ATTACHMENTS'	=> $multi_attachments,
		);
	}

	/**
	 * @param array $poster_info
	 * @return int
	 */
	protected function get_poster_warnings(array $poster_info)
	{
		return ($this->auth->acl_get('m_warn') && !empty($poster_info['warnings'])) ? $poster_info['warnings'] : 0;
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	protected function get_report_status(array $row)
	{
		return ($row['post_reported'] && $this->auth->acl_get('m_report', $row['forum_id'])) ? true : false;
	}

	/**
	 * @param string $topic_url
	 * @param int $start
	 * @return void
	 */
	protected function set_form_action($topic_url, $start)
	{
		$this->template->assign_var('S_TOPIC_ACTION', append_sid($topic_url, (($start == 0) ? '' : "start=$start")) . '#comments');
	}
}
