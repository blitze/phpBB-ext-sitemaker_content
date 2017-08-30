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

class filters implements EventSubscriberInterface
{
	/** @var \blitze\sitemaker\services\date_range */
	protected $date_range;

	/**
	 * Constructor
	 *
	 * @param \blitze\sitemaker\services\date_range		$date_range			Date Range Object
	 */
	public function __construct(\blitze\sitemaker\services\date_range $date_range)
	{
		$this->date_range = $date_range;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'blitze.content.view.filter' => 'archive_filter',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function archive_filter(\phpbb\event\data $event)
	{
		if ($event['filter_type'] === 'archive')
		{
			$sql_array = $event['sql_array'];
			$month = array_combine(array('year', 'mon'), explode('-', $event['filter_value']));
			$range = $this->date_range->get_month($month);

			$sql_array['WHERE'][] = "t.topic_time BETWEEN {$range['start']} AND {$range['stop']}";

			$event['sql_array'] = $sql_array;
		}
	}
}
