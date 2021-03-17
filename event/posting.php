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
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\form\builder */
	protected $builder;

	/** @var string */
	protected $content_langname = '';

	/** @var string */
	protected $content_type = '';

	/** @var bool */
	protected $build_content = false;

	/** @var int */
	protected $content_post_id = 0;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper					$helper			Controller helper class
	 * @param \phpbb\template\template					$template		Template object
	 * @param \blitze\content\services\form\builder		$builder		Form builder object
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \blitze\content\services\form\builder $builder)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->builder = $builder;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.modify_posting_auth'					=> 'init_builder',
			'core.posting_modify_message_text'			=> 'build_message',
			'core.posting_modify_submission_errors'		=> 'show_errors',
			'core.submit_post_modify_sql_data'			=> 'modify_sql_data',
			'core.posting_modify_submit_post_after'		=> array(array('save_fields'), array('set_redirect_url')),
			'core.topic_review_modify_post_list'		=> 'set_content_post_id',
			'core.topic_review_modify_row'				=> 'modify_topic_review',
			'core.posting_modify_submit_post_before'	=> 'force_visibility',
			'core.posting_modify_template_vars'			=> array(array('build_form'), array('get_form', -100)),
			'core.page_footer'							=> 'update_navbar',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function init_builder(\phpbb\event\data $event)
	{
		$type_info = $this->builder->init($event['forum_id'], $event['topic_id'], $event['mode'], $event['save']);

		$this->content_type = (string) $type_info[0];
		$this->content_langname = $type_info[1];

		// are we adding/editing a content type post?
		if (empty($event['post_data']['topic_first_post_id']) || $event['post_data']['topic_first_post_id'] == $event['post_id'])
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
	public function force_visibility(\phpbb\event\data $event)
	{
		if ($this->build_content)
		{
			$data = (array) $event['data'];
			$this->builder->force_visibility($event['mode'], $data);

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
			$this->builder->modify_sql_data($sql_data[TOPICS_TABLE]['sql']);

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
			$post_list = (array) $event['post_list'];
			$this->content_post_id = array_pop($post_list);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_topic_review(\phpbb\event\data $event)
	{
		if ($this->content_type && $event['row']['post_id'] == $this->content_post_id)
		{
			$post_row = (array) $event['post_row'];
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
		// are we posting/editing a content type post?
		if ($this->build_content && ($redirect_url = $this->builder->get_redirect_url()) !== '')
		{
			$event['redirect_url'] = str_replace('&amp;', '&', urldecode($redirect_url));
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
			$this->builder->save_db_fields(array_merge((array) $event['post_data'], (array) $event['data']));
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
			$post_data = (array) $event['post_data'];
			$page_data = (array) $event['page_data'];
			$this->builder->generate_form((int) $event['topic_id'], $post_data, $page_data);
		}
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function get_form(\phpbb\event\data $event)
	{
		if ($this->build_content)
		{
			$post_data = (array) $event['post_data'];
			$page_data = (array) $event['page_data'];

			$post_data['TOPIC_URL'] = './';
			$page_data['SITEMAKER_FORM'] = true;

			$this->builder->build_form();

			if ($event['preview'] && $this->content_type)
			{
				$page_data['PREVIEW_MESSAGE'] = $this->builder->generate_preview($this->content_type, $post_data);
			}

			$event['page_data'] = $page_data;
			unset($post_data, $page_data);
		}
	}

	/**
	 * @return void
	 */
	public function update_navbar()
	{
		if ($this->content_type)
		{
			// remove 'Forum' nav added by Sitemaker when a startpage is specified
			if ($this->template->find_key_index('navlinks', 1))
			{
				$this->template->alter_block_array('navlinks', array(), 1, 'delete');
			}

			// update label & url that currently points to the forum to now point to the content type
			$this->template->alter_block_array('navlinks', array(
				'FORUM_NAME'	=> $this->content_langname,
				'U_VIEW_FORUM'	=> $this->helper->route('blitze_content_type', array('type' => $this->content_type)),
			), 0, 'change');
		}
	}
}
