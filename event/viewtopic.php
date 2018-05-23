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

class viewtopic implements EventSubscriberInterface
{
	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper				$helper				Helper object
	 * @param \phpbb\request\request_interface		$request			Request object
	 * @param \blitze\content\services\types		$content_types		Content types object
	*/
	public function __construct(\phpbb\controller\helper $helper, \phpbb\request\request_interface $request, \blitze\content\services\types $content_types)
	{
		$this->helper = $helper;
		$this->request = $request;
		$this->content_types = $content_types;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.viewforum_get_topic_data'					=> 'viewforum_redirect',
			'core.viewtopic_assign_template_vars_before'	=> 'viewtopic_redirect',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function viewforum_redirect(\phpbb\event\data $event)
	{
		if ($type = $this->content_types->get_forum_type($event['forum_data']['forum_id']))
		{
			redirect($this->helper->route('blitze_content_type', array(
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
		if (($type = $this->content_types->get_forum_type($event['forum_id'])) && !$this->request->is_set_post('update'))
		{
			$topic_url = $this->helper->route('blitze_content_show', array(
				'type'		=> $type,
				'topic_id'	=> $event['topic_id'],
				'slug'		=> $event['topic_data']['topic_slug']
			));

			$post_id = $event['post_id'];
			if ($post_id && $post_id != $event['topic_data']['topic_first_post_id'])
			{
				$topic_url .= "?p=$post_id#p$post_id";
			}

			redirect($topic_url);
		}
	}
}
