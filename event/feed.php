<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class feed implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config					$config				Config object
	 * @param \phpbb\db\driver\driver_interface		$db					Database connection
	 * @param \phpbb\template\template				$template			Template object
	 * @param \blitze\content\services\types		$content_types		Content types object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \blitze\content\services\types $content_types)
	{
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->content_types = $content_types;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.feed_base_modify_item_sql'	=> 'hide_from_feeds',
			'core.page_header_after'			=> 'set_content_feeds',
		);
	}

	/**
	 * This excludes content forums from feeds
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function hide_from_feeds(\phpbb\event\data $event)
	{
		$sql_ary = $event['sql_ary'];

		$forum_ids = array_keys($this->content_types->get_forum_types());
		$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('p.forum_id', array_map('intval', $forum_ids), true);

		$event['sql_ary'] = $sql_ary;
	}

	/**
	 * @return void
	 */
	public function set_content_feeds()
	{
		if ($this->config['feed_enable'])
		{
			$types_ary = $this->content_types->get_all_types();

			$feeds = array();
			foreach ($types_ary as $entity)
			{
				/** @var \blitze\content\model\entity\type $entity */
				$feeds[] = array(
					'type'		=> $entity->get_content_name(),
					'langname'	=> $entity->get_content_langname(),
				);
			}

			$this->template->assign_vars(array('CONTENT_FEEDS' => $feeds));
		}
	}
}
