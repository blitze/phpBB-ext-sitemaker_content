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
	/** @var \blitze\content\services\form\builder */
	protected $builder;

	/** @var string */
	protected $content_type = '';

	/** @var bool */
	protected $build_content = false;

	/** @var int */
	protected $content_post_id = 0;

	/**
	 * Constructor
	 *
	 * @param \blitze\content\services\form\builder		$builder		Form builder object
	*/
	public function __construct(\blitze\content\services\form\builder $builder)
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
			'core.posting_modify_submit_post_after'		=> array('save_fields', 'set_redirect_url'),
			'core.topic_review_modify_post_list'		=> 'set_content_post_id',
			'core.topic_review_modify_row'				=> 'modify_topic_review',
			'core.posting_modify_template_vars'			=> 'build_form',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function init_builder(\phpbb\event\data $event)
	{
		$this->content_type = $this->builder->init($event['forum_id'], $event['topic_id'], $event['mode'], $event['save']);

		$topic_first_post_id = $event['post_data']['topic_first_post_id'];
		if (!$topic_first_post_id || $topic_first_post_id == $event['post_id'])
		{
			$this->build_content = ($this->content_type && $event['mode'] !== 'reply') ? true : false;
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function build_message(\phpbb\event\data $event)
	{
		if ($this->build_content)
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
		if ($this->build_content)
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
		if ($this->build_content)
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
		if ($this->build_content)
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
	public function set_content_post_id(\phpbb\event\data $event)
	{
		if ($this->content_type)
		{
			$this->content_post_id = array_pop($event['post_list']);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_topic_review(\phpbb\event\data $event)
	{
		if ($this->content_type && $event['row']['post_id'] === $this->content_post_id)
		{
			$post_row = $event['post_row'];
			$post_row['MESSAGE'] = $this->builder->get_content_view($this->content_type, $post_row, 'summary');
			$event['post_row'] = $post_row;
			unset($post_row);
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
			if ($this->build_content)
			{
				$event['redirect_url'] = $this->builder->get_cp_url();
			}
			else
			{
				$topic_url = $this->builder->get_post_url($this->content_type, $event['post_data']);

				$post_id = $event['post_id'];
				if ($post_id != $event['post_data']['topic_first_post_id'])
				{
					$topic_url .= "?p=$post_id#p$post_id";
				}

				$event['redirect_url'] = $topic_url;
			}
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function save_fields(\phpbb\event\data $event)
	{
		if ($this->build_content && in_array($event['mode'], array('post', 'edit', 'save')))
		{
			$this->builder->save_db_fields($event['topic_id']);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function build_form(\phpbb\event\data $event)
	{
		if ($this->build_content)
		{
			$post_data = $event['post_data'];
			$page_data = $event['page_data'];

			$post_data['TOPIC_URL'] = './';
			$page_data['SITEMAKER_FORM'] = $this->builder->generate_form($event['topic_id'], $post_data, $page_data);

			if ($event['preview'])
			{
				$page_data['PREVIEW_MESSAGE'] = $this->builder->generate_preview($this->content_type, $post_data);
			}

			$event['page_data'] = $page_data;
			unset($post_data, $page_data);
		}
	}
}
