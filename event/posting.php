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

class posting implements EventSubscriberInterface
{
	/** @var \blitze\content\services\builder */
	protected $builder;

	/** @var string */
	protected $content_type;

	/**
	 * Constructor
	 *
	 * @param \blitze\content\services\builder		$builder		Form builder object
	*/
	public function __construct(\blitze\content\services\builder $builder)
	{
		$this->builder = $builder;
	}

	/**
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.modify_posting_auth'					=> 'init_builder',
			'core.posting_modify_message_text'			=> 'build_message',
			'core.posting_modify_submission_errors'		=> 'show_errors',
			'core.posting_modify_submit_post_before'	=> 'modify_post_data',
			'core.submit_post_modify_sql_data'			=> 'modify_sql_data',
			'core.posting_modify_submit_post_after'		=> 'set_redirect_url',
			'core.posting_modify_template_vars'			=> 'build_form',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function init_builder(\phpbb\event\data $event)
	{
		$this->content_type = $this->builder->init($event['forum_id'], $event['mode'], $event['save']);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function build_message(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$message_parser = $event['message_parser'];
			$message_parser->message = $this->builder->generate_message();
			$event['message_parser'] = $message_parser;
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function show_errors(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$event['error'] = $this->builder->get_errors();
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_post_data(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$data = $event['data'];
			$this->builder->force_visibility($data);
			$event['data'] = $data;
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_sql_data(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$sql_data = $event['sql_data'];
			$this->builder->modify_posting_data($sql_data[TOPICS_TABLE]['sql']);
			$event['sql_data'] = $sql_data;
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function set_redirect_url(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$event['redirect_url'] = $this->builder->get_redirect_url();
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function build_form(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$post_data = $event['post_data'];
			$page_data = $event['page_data'];

			$page_data['SITEMAKER_FORM'] = $this->builder->generate_form($event['topic_id'], $post_data, $page_data);

			if ($event['preview'])
			{
				$page_data['PREVIEW_SUBJECT'] = '';
				$page_data['PREVIEW_MESSAGE'] = $this->builder->generate_preview($this->content_type, $post_data);
			}

			$event['page_data'] = $page_data;
		}
	}
}
