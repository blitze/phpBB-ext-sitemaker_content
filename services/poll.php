<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class poll
{
	/** @var \blitze\sitemaker\services\poll */
	protected $poll;

	/** @var \blitze\sitemaker\services\template */
	protected $ptemplate;

	/**
	 * Constructor
	 *
	 * @param \blitze\sitemaker\services\poll		$poll			Poll Object
	 * @param \blitze\sitemaker\services\template	$ptemplate		Sitemaker Template Object
	*/
	public function __construct(\blitze\sitemaker\services\poll $poll, \blitze\sitemaker\services\template $ptemplate)
	{
		$this->poll = $poll;
		$this->ptemplate = $ptemplate;
	}

	/**
	 * @param array $topic_data
	 * @return string
	 */
	public function display(array $topic_data)
	{
		$content = '';
		if ($topic_data['poll_start'])
		{
			$this->poll->build($topic_data, $this->ptemplate);

			$content = $this->ptemplate->render_view('blitze/sitemaker', 'blocks/forum_poll.html', 'topic_poll');
		}

		return $content;
	}
}
