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

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \blitze\sitemaker\services\poll		$poll			Poll Object
	 * @param \phpbb\template\template	$template		Template Object
	 */
	public function __construct(\blitze\sitemaker\services\poll $poll, \phpbb\template\template $template)
	{
		$this->poll = $poll;
		$this->template = $template;
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
			$this->poll->build($topic_data, $this->template);

			$content = $this->template->render_view('blitze/sitemaker', 'blocks/forum_poll.html', 'topic_poll');
		}

		return $content;
	}
}
