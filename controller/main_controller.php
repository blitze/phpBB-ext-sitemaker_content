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

	/* @var \blitze\content\services\comments */
	protected $comments;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\content\services\views\views_factory */
	protected $views_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface				$db					Database object
	 * @param \phpbb\controller\helper						$helper				Helper object
	 * @param \phpbb\request\request_interface				$request			Request object
	 * @param \phpbb\template\template						$template			Template object
	 * @param \phpbb\user									$user				User object
	 * @param \blitze\content\services\comments				$comments			Comments object
	 * @param \blitze\content\services\types				$content_types		Content types object
	 * @param \blitze\content\services\views\views_factory	$views_factory		Views handlers
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\comments $comments, \blitze\content\services\types $content_types, \blitze\content\services\views\views_factory $views_factory)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->comments = $comments;
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
	public function index($type, $page = 1, $filter_type = '', $filter_value = '')
	{
		$entity = $this->get_type_entity($type);
		$content_langname = $entity->get_content_langname();

		if ($entity->get_index_show_desc())
		{
			$this->template->assign_vars(array(
				'CONTENT_TYPE'		=> $content_langname,
				'CONTENT_DESC'		=> $entity->get_content_desc(),
			));
		}

		$view_handler = $this->views_factory->get($entity->get_content_view());
		$view_handler->render_index($entity, $page, $filter_type, $filter_value);

		return $this->helper->render($view_handler->get_index_template(), $content_langname);
	}

	/**
	 * Display a single topic
	 *
	 * @param string $type
	 * @param string $slug
	 * @param int $topic_id
	 * @param int $page
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function show($type, $slug, $topic_id)
	{
		$entity = $this->get_type_entity($type);

		$update_count = array();
		$view_handler = $this->views_factory->get($entity->get_content_view());
		$topic_data = $view_handler->render_detail($entity, $topic_id, $update_count);

		$this->add_navlink($topic_data['topic_title'], $topic_data['topic_url']);
		$this->update_views($topic_id, $update_count);

		if ($entity->get_allow_comments())
		{
			$this->comments->show($topic_id, $topic_data['total_comments'], $topic_data['topic_url'], $topic_data);
		}

		return $this->helper->render($view_handler->get_detail_template(), $topic_data['topic_title']);
	}

	/**
	 * @param string $type
	 * @return \blitze\content\model\entity\type
	 */
	protected function get_type_entity($type)
	{
		$entity = $this->content_types->get_type($type);

		$this->add_navlink($entity->get_content_langname(), $this->helper->route('blitze_content_index', array('type' => $type)));

		$this->template->assign_vars(array(
			'S_COMMENTS'	=> $entity->get_allow_comments(),
			'S_VIEWS'		=> $entity->get_allow_views(),
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
