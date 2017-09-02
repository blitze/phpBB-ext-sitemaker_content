<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\controller;

class main_controller
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/* @var \blitze\content\services\comments\comments_interface */
	protected $comments;

	/** @var \blitze\content\services\poll */
	protected $poll;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\content\services\views\views_factory */
	protected $views_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface						$db					Database object
	 * @param \phpbb\controller\helper								$helper				Helper object
	 * @param \phpbb\request\request_interface						$request			Request object
	 * @param \phpbb\template\template								$template			Template object
	 * @param \phpbb\user											$user				User object
	 * @param \blitze\content\services\comments\comments_interface	$comments			Comments object
	 * @param \blitze\content\services\poll							$poll				Poll object
	 * @param \blitze\content\services\types						$content_types		Content types object
	 * @param \blitze\content\services\views\views_factory			$views_factory		Views handlers
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\comments\comments_interface $comments, \blitze\content\services\poll $poll, \blitze\content\services\types $content_types, \blitze\content\services\views\views_factory $views_factory)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->comments = $comments;
		$this->poll = $poll;
		$this->content_types = $content_types;
		$this->views_factory = $views_factory;
	}

	/**
	 * Display list of topics for content type
	 *
	 * @param string $type
	 * @param int $page
	 * @param string $filter_type
	 * @param string $filter_value
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function index($type, $page, $filter_type, $filter_value)
	{
		$entity = $this->get_type_entity($type);

		$this->template->assign_vars($entity->to_array());

		$view_handler = $this->views_factory->get($entity->get_content_view());
		$view_handler->render_index($entity, $page, $filter_type, $filter_value);

		return $this->helper->render($view_handler->get_index_template(), $entity->get_content_langname());
	}

	/**
	 * Filter topics by a filter type
	 *
	 * @param string $filter_type
	 * @param string $filter_value
	 * @param int $page
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function filter($filter_type, $filter_value, $page)
	{
        /** @var \blitze\content\services\views\driver\portal $view_handler */
        $view_handler = $this->views_factory->get('blitze.content.view.portal');
		$view_handler->render_filter($filter_type, $filter_value, $page);

		return $this->helper->render($view_handler->get_index_template());
	}

	/**
	 * Display a single topic
	 *
	 * @param string $type
	 * @param int $topic_id
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function show($type, $topic_id)
	{
		$view = $this->request->variable('view', '');
		$entity = $this->get_type_entity($type);

		$update_count = array();
		$view_handler = $this->views_factory->get($entity->get_content_view());
		$topic_data = $view_handler->render_detail($entity, $topic_id, $update_count);

		$this->add_navlink($topic_data['topic_title'], $topic_data['topic_url']);
		$this->template->assign_var('TOPIC_POLL', $this->poll->display($topic_data));
		$this->template->assign_vars($entity->to_array());

		if ($entity->get_allow_comments())
		{
			$this->comments->show_comments($type, $topic_data, $update_count);
			$this->comments->show_form($topic_data);
		}

		if ($view !== 'print')
		{
			$this->update_views($topic_id, $update_count);
			$template_file = $view_handler->get_detail_template();
		}
		else
		{
			$template_file = 'views/print.html';
			$this->template->assign_var('TOPIC_URL', generate_board_url(true) . $topic_data['topic_url']);
		}

		return $this->helper->render($template_file, $topic_data['topic_title']);
	}

	/**
	 * @param string $type
	 * @return \blitze\content\model\entity\type
	 */
	protected function get_type_entity($type)
	{
		/** @var \blitze\content\model\entity\type $entity */
		$entity = $this->content_types->get_type($type, true);

		$this->add_navlink($entity->get_content_langname(), $this->helper->route('blitze_content_index', array('type' => $type)));

		$this->template->assign_vars(array(
			'S_COMMENTS'	=> $entity->get_allow_comments(),
			'S_VIEWS'		=> $entity->get_allow_views(),
			'S_TOOLS'		=> true,
		));

		return $entity;
	}

	/**
	 * @param string $title
	 * @param string $url
	 * @return void
	 */
	protected function add_navlink($title, $url)
	{
		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $title,
			'U_VIEW_FORUM'	=> $url,
		));
	}

	/**
	 * Update topic view and if necessary attachment view counters ... but only for humans and if this is the first 'page view'
	 * @param int $topic_id
	 * @param array $update_count
	 * @return void
	 */
	protected function update_views($topic_id, array $update_count)
	{
		if (!$this->user->data['is_bot'] && !$this->request->is_set('page'))
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_views = topic_views + 1, topic_last_view_time = ' . time() . "
				WHERE topic_id = $topic_id";
			$this->db->sql_query($sql);

			// Update the attachment download counts
			if (sizeof($update_count))
			{
				$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
					SET download_count = download_count + 1
					WHERE ' . $this->db->sql_in_set('attach_id', array_unique($update_count));
				$this->db->sql_query($sql);
			}
		}
	}
}
