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

class search implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\controller\helper				$helper				Helper object
	 * @param \blitze\content\services\types		$content_types		Content types object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \blitze\content\services\types $content_types)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->content_types = $content_types;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.search_get_posts_data'	=> 'modify_posts_data',
			'core.search_get_topic_data'	=> 'modify_topic_data',
			'core.search_modify_tpl_ary'	=> 'modify_tpl_data',
		);
	}

	/**
	 * Since this extension allows for scheduling articles,
	 * We want to make sure that we do not display future articles in search results
	 *
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
	 * Since this extension allows for scheduling articles,
	 * We want to make sure that we do not display future articles in search results
	 *
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
	public function modify_tpl_data(\phpbb\event\data $event)
	{
		$row = $event['row'];
		if ($type = $this->content_types->get_forum_type($row['forum_id']))
		{
			$tpl_ary = $event['tpl_ary'];

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
			$forum_url = $this->helper->route('blitze_content_type', array(
				'type' => $type
			));

			$tpl_ary['MESSAGE'] = $this->modify_message($row, $tpl_ary['MESSAGE']);
			$tpl_ary['U_VIEW_TOPIC'] = $tpl_ary['U_VIEW_POST'] = $topic_url;
			$tpl_ary['U_VIEW_FORUM'] = $forum_url;

			$event['tpl_ary'] = $tpl_ary;
			unset($tpl_ary);
		}
	}

	/**
	 * @param array $row
	 * @param string $message
	 * @return string
	 */
	public function modify_message(array $row, $message)
	{
		$patterns = array(
			'#(&\#91;)pagebreak\s?(title=(.*?))(&\#93;)#s',
			'#<!-- begin field --><h4>(.*?)</h4><br>#s',
		);

		return ($row['post_id'] === $row['topic_first_post_id']) ? preg_replace($patterns, ' ', $message) : $message;
	}
}
