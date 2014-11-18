<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;

	/* @var \primetime\content\services\types */
	protected $content_types;

	/* @var \primetime\content\services\displayer */
	protected $displayer;

	/** @var string */
	protected $root_path;

	/* @var string */
	protected $php_ext;

	/* @var array */
	protected $content_forums;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\db						$config				Config object
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\controller\helper				$helper				Helper object
	 * @param \phpbb\user							$user				User object
	 * @param \primetime\content\services\types		$content_types		Content types object
	 * @param \primetime\content\services\displayer	$displayer			Content displayer object
	*/
	public function __construct(\phpbb\config\db $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\user $user, \primetime\content\services\types $content_types, \primetime\content\services\displayer $displayer, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->user = $user;
		$this->content_types = $content_types;
		$this->displayer = $displayer;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->content_forums = unserialize($config['primetime_content_forums']);
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.search_get_posts_data'	=> 'modify_posts_data',
			'core.search_get_topic_data'	=> 'modify_topic_data',
			'core.search_modify_tpl_ary'	=> 'content_search',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_redirect',
			'core.posting_modify_template_vars'				=> 'posting_redirect',
			'core.viewonline_overwrite_location'			=> 'add_viewonline_location',
		);
	}

	public function modify_posts_data($event)
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

	public function modify_topic_data($event)
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

	public function content_search($event)
	{
		$tpl_ary = $event['tpl_ary'];
		$forum_id = $tpl_ary['FORUM_ID'];
		$topic_id = $tpl_ary['TOPIC_ID'];
		$message = &$tpl_ary['MESSAGE'];

		if (isset($this->content_forums[$forum_id]))
		{
			$type = $this->content_forums[$forum_id];

			$forum_url = $this->helper->route('primetime_content_index', array(
				'type'		=> $type
			));

			$topic_url = $this->helper->route('primetime_content_show', array(
				'type'		=> $type,
				'topic_id'	=> $topic_id,
				'slug'		=> $event['row']['topic_slug']
			));

			$tpl_ary['U_VIEW_TOPIC'] = $tpl_ary['U_VIEW_POST'] = $topic_url;
			$tpl_ary['U_VIEW_FORUM'] = $forum_url;

			$event['tpl_ary'] = $tpl_ary;
			unset($type, $tpl_ary);
		}
	}

	public function viewtopic_redirect($event)
	{
		if (isset($this->content_forums[$event['forum_id']]))
		{
			$type = $this->content_forums[$event['forum_id']];

			redirect($this->helper->route('primetime_content_show', array(
				'type'		=> $type,
				'topic_id'	=> $event['topic_id'],
				'slug'		=> $event['topic_data']['topic_slug']
			)));
		}
	}

	public function posting_redirect($event)
	{
		if (isset($this->content_forums[$event['forum_id']]))
		{
			$type = $this->content_forums[$event['forum_id']];

			redirect(append_sid("{$this->root_path}mcp.$this->php_ext", "i=-primetime-content-mcp-content_module&mode=content&action=edit&type={$type}&t=" . $event['topic_id']));
		}
	}

	public function add_viewonline_location($event)
	{
		if ($event['on_page'][1] == 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/content/') === 0)
		{
			$types = join('|', $this->content_forums);
			preg_match("/\/content\/($types)(\/[0-9]\/.*)?/is", $event['row']['session_page'], $match);

			if (sizeof($match))
			{
				$row = $this->content_types->get_type($match[1]);
				$lang = (!empty($match[2])) ? 'HOME' : 'INDEX';

				$event['location'] = sprintf($this->user->lang($lang), $row['content_langname']);
				$event['location_url'] = $event['row']['session_page'];
				unset($row);
			}
		}
	}
}
