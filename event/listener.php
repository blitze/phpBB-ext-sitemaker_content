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

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var string */
	protected $phpbb_root_path;

	/* @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\controller\helper				$helper				Helper object
	 * @param \phpbb\language\language				$language			Language object
	 * @param \blitze\content\services\types		$content_types		Content types object
	 * @param \blitze\content\services\fields		$fields				Content fields object
	 * @param string								$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string								$php_ext			php file extension
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\language\language $language, \blitze\content\services\types $content_types, \blitze\content\services\fields $fields, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->language = $language;
		$this->content_types = $content_types;
		$this->fields = $fields;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'				                => 'load_block_language',
			'core.search_get_posts_data'	                => 'modify_posts_data',
			'core.search_get_topic_data'	                => 'modify_topic_data',
			'core.search_modify_tpl_ary'	                => 'content_search',
			'core.viewforum_get_topic_data'					=> 'viewforum_redirect',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_redirect',
			'core.make_jumpbox_modify_forum_list'			=> 'update_jumpbox',
			'core.viewonline_overwrite_location'			=> 'add_viewonline_location',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function load_block_language(\phpbb\event\data $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'blitze/content',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_posts_data(\phpbb\event\data $event)
	{
		$sql_array = $event['sql_array'];
		$sql_array['WHERE'] .= ' AND t.topic_time <= ' . time();

		$sql_count = $sql_array;
		$sql_count['SELECT'] = 'COUNT(p.post_id) AS total_results';

		$sql = $this->db->sql_build_query('SELECT', $sql_count);
		$result = $this->db->sql_query($sql);
		$total_results = $this->db->sql_fetchfield('total_results');
		$this->db->sql_freeresult($result);

		$event['sql_array'] = $sql_array;
		$event['total_match_count'] = $total_results;
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_topic_data(\phpbb\event\data $event)
	{
		$sql_where = $event['sql_where'];
		$sql_where .= ' AND t.topic_time <= ' . time();

		$sql = 'SELECT COUNT(t.topic_id) AS total_results FROM ' . $event['sql_from'] . ' WHERE ' . $sql_where;
		$result = $this->db->sql_query($sql);
		$total_results = $this->db->sql_fetchfield('total_results');
		$this->db->sql_freeresult($result);

		$event['sql_where'] = $sql_where;
		$event['total_match_count'] = $total_results;
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function content_search(\phpbb\event\data $event)
	{
		$row = $event['row'];
		$tpl_ary = $event['tpl_ary'];

		$forum_id = $row['forum_id'];

		if ($type = $this->content_types->get_forum_type($forum_id))
		{
			$params = array(
				'type'		=> $type,
				'topic_id'	=> $row['topic_id'],
				'slug'		=> $event['row']['topic_slug']
			);

			if (isset($row['post_id']) && $row['topic_first_post_id'] !== $row['post_id'])
			{
				$params += array(
					'p'	=> $row['post_id'],
					'#'	=> "p{$row['post_id']}",
				);
			}

			$topic_url = $this->helper->route('blitze_content_show', $params);
			$forum_url = $this->helper->route('blitze_content_index', array(
				'type'		=> $type
			));

			$tpl_ary['U_VIEW_TOPIC'] = $tpl_ary['U_VIEW_POST'] = $topic_url;
			$tpl_ary['U_VIEW_FORUM'] = $forum_url;

			$event['tpl_ary'] = $tpl_ary;
			unset($type, $tpl_ary);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function viewforum_redirect(\phpbb\event\data $event)
	{
		if ($type = $this->content_types->get_forum_type($event['forum_data']['forum_id']))
		{
			redirect($this->helper->route('blitze_content_index', array(
				'type' => $type,
			)));
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function viewtopic_redirect(\phpbb\event\data $event)
	{
		if ($type = $this->content_types->get_forum_type($event['forum_id']))
		{
			redirect($this->helper->route('blitze_content_show', array(
				'type'		=> $type,
				'topic_id'	=> $event['topic_id'],
				'slug'		=> $event['topic_data']['topic_slug']
			)));
		}
	}

	/**
	 * Remove content forums from forum jumpbox
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function update_jumpbox(\phpbb\event\data $event)
	{
		$event['rowset'] = array_diff_key($event['rowset'], $this->content_types->get_forum_types());
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function add_viewonline_location(\phpbb\event\data $event)
	{
		if ($event['on_page'][1] == 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/content/') === 0)
		{
			$types = join('|', $this->content_types->get_forum_types());
			preg_match("/\/content\/($types)(\/[0-9]\/.*)?/is", $event['row']['session_page'], $match);

			if (sizeof($match))
			{
				$row = $this->content_types->get_type($match[1]);
				$lang = (!empty($match[2])) ? 'SITEMAKER_READING_TOPIC' : 'SITEMAKER_BROWSING_CONTENT';

				$event['location'] = $this->language->lang($lang, $row['content_langname']);
				$event['location_url'] = $event['row']['session_page'];
				unset($row);
			}
		}
	}
}
