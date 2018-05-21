<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class mcp_topic implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	private $type;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \blitze\content\services\types		$content_types		Content types object
	 * @param \blitze\content\services\fields		$fields				Content fields object
	 * @param string								$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string								$php_ext			php file extension
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \blitze\content\services\types $content_types, \blitze\content\services\fields $fields, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->content_types = $content_types;
		$this->fields = $fields;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.mcp_topic_modify_post_data'	=> 'modify_post_data',
			'core.mcp_topic_review_modify_row'	=> 'modify_review_row',
			'core.mcp_view_forum_modify_sql'	=> 'modify_forum_sql',
			'core.mcp_queue_get_posts_for_topics_query_before'	=> 'modify_topic_queue_sql',
			'core.mcp_front_queue_unapproved_total_before'		=> 'modify_forumlist',
			'core.mcp_front_view_queue_postid_list_after'		=> 'modify_forumlist',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_post_data(\phpbb\event\data $event)
	{
		$forum_type = $this->content_types->get_forum_type($event['rowset'][0]['forum_id']);
		$this->type = $forum_type ?: '';
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_review_row(\phpbb\event\data $event)
	{
		if ($this->type && $event['row']['post_id'] === $event['topic_info']['topic_first_post_id'] && ($entity = $this->content_types->get_type($this->type)) !== false)
		{
			$this->fields->prepare_to_show($entity, array($event['topic_info']['topic_id']), $entity->get_summary_fields(), $entity->get_summary_tpl(), 'summary');
			$users_cache = $attachments = $topic_tracking_info = $update_count = array();

			$post_row = (array) $event['post_row'];
			$topic_data = $event['topic_info'];
			$post_data = array_merge($post_row, $event['row']);
			$users_cache[$post_data['poster_id']] = array();

			$tpl_data = $this->fields->get_summary_template_data($this->type, $topic_data, $post_data, $users_cache, $attachments, $topic_tracking_info, $update_count);
			$content = $this->fields->build_content($tpl_data);

			$post_row['MESSAGE'] = isset($content['CUSTOM_DISPLAY']) ? $content['CUSTOM_DISPLAY'] : join('', $content['FIELDS']['all']);
			$event['post_row'] = $post_row;
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_forum_sql(\phpbb\event\data $event)
	{
		$types = $this->content_types->get_forum_types();

		if ($types[$event['forum_id']])
		{
			redirect(append_sid("{$this->phpbb_root_path}mcp.$this->php_ext"));
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_topic_queue_sql(\phpbb\event\data $event)
	{
		$forum_list = $event['forum_list'];
		$forum_list = is_array($forum_list) ? $forum_list : array($forum_list);

		$sql = 'SELECT t.forum_id, t.topic_id, t.topic_title, t.topic_title AS post_subject, t.topic_time AS post_time, t.topic_poster AS poster_id, t.topic_first_post_id AS post_id, t.topic_attachment AS post_attachment, t.topic_first_poster_name AS username, t.topic_first_poster_colour AS user_colour
			FROM ' . TOPICS_TABLE . ' t
			WHERE ' . $this->db->sql_in_set('forum_id', $this->get_forum_list($forum_list)) . '
				AND  ' . $this->db->sql_in_set('topic_visibility', $event['visibility_const']) . "
				AND topic_delete_user <> 0
				{$event['limit_time_sql']}
			ORDER BY {$event['sort_order_sql']}";

		$event['sql'] = $sql;
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_forumlist(\phpbb\event\data $event)
	{
		$event['forum_list'] = $this->get_forum_list((array) $event['forum_list']);
	}

	/**
	 * @param array
	 * @return array
	 */
	protected function get_forum_list(array $forum_list)
	{
		$types = $this->content_types->get_forum_types();
		$forum_ids = array_diff($forum_list, array_keys($types));

		return sizeof($forum_ids) ? $forum_ids : array(0);
	}
}
