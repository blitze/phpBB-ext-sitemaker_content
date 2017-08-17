<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\comments;

class form
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth				$auth				Auth object
	 * @param \phpbb\config\config			$config				Config object
	 * @param \phpbb\language\language		$language			Language Object
	 * @param \phpbb\template\template		$template			Template object
	 * @param \phpbb\user					$user				User object
	 * @param string						$root_path			Path to the phpbb includes directory.
	 * @param string						$php_ext			php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function show_form(array $topic_data)
	{
		if (!$this->user_can_post_comment($topic_data))
		{
			add_form_key('posting');

			$qr_hidden_fields = array(
				'topic_cur_post_id'		=> (int) $topic_data['topic_last_post_id'],
				'lastclick'				=> (int) time(),
				'topic_id'				=> (int) $topic_data['topic_id'],
				'forum_id'				=> (int) $topic_data['forum_id'],
			);

			$this->set_smilies($topic_data['forum_id'], $qr_hidden_fields);
			$this->set_bbcode($topic_data['forum_id'], $qr_hidden_fields);
			$this->set_notification($s_watching_topic['is_watching'], $qr_hidden_fields);
			$this->set_topic_lock($topic_data['topic_status'], $qr_hidden_fields);
			$this->set_magic_urls($qr_hidden_fields);

			$this->template->assign_vars(array(
				'S_QUICK_REPLY'			=> true,
				'L_QUICKREPLY'			=> $this->language->lang('NEW_COMMENT'),
				'U_QR_ACTION'			=> append_sid("{$this->phpbb_root_path}posting.{$this->php_ext}", "mode=reply&amp;f={$topic_data['forum_id']}&amp;t={$topic_data['topic_id']}"),
				'QR_HIDDEN_FIELDS'		=> build_hidden_fields($qr_hidden_fields),
				'SUBJECT'				=> 'Re: ' . censor_text($topic_data['topic_title']),
			));
		}
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function user_can_post_comment(array $topic_data)
	{
		if ($this->user->data['is_registered'] && ($topic_data['forum_flags'] & FORUM_FLAG_QUICK_REPLY) && $this->auth->acl_get('f_reply', $topic_data['forum_id']))
		{
			return !$this->topic_is_locked($topic_data);
		}
		return false;
	}

	/**
	 * @param array $topic_data
	 * @return bool
	 */
	protected function topic_is_locked(array $topic_data)
	{
		return (($topic_data['forum_status'] == ITEM_UNLOCKED && $topic_data['topic_status'] == ITEM_UNLOCKED) || $this->auth->acl_get('m_edit', $topic_data['forum_id'])) ? true : false;
	}

	/**
	 * @param int $forum_id
	 * @param array $qr_hidden_fields
	 * @return bool
	 */
	protected function set_smilies($forum_id, array &$qr_hidden_fields)
	{
		if (!($this->config['allow_smilies'] && $this->user->optionget('smilies') && $this->auth->acl_get('f_smilies', $forum_id)))
		{
			$qr_hidden_fields['disable_smilies'] = 1;
		}
	}

	/**
	 * @param int $forum_id
	 * @param array $qr_hidden_fields
	 * @return bool
	 */
	protected function set_bbcode($forum_id, array &$qr_hidden_fields)
	{
		if (!($this->config['allow_bbcode'] && $this->user->optionget('bbcode') && $this->auth->acl_get('f_bbcode', $forum_id)))
		{
			$qr_hidden_fields['disable_bbcode'] = 1;
		}
	}

	/**
	 * @param bool $is_watching
	 * @param array $qr_hidden_fields
	 * @return bool
	 */
	protected function set_notification($is_watching, array &$qr_hidden_fields)
	{
		if ($this->config['allow_topic_notify'] && ($this->user->data['user_notify'] || $is_watching))
		{
			$qr_hidden_fields['notify'] = 1;
		}
	}

	/**
	 * @param int $topic_status
	 * @param array $qr_hidden_fields
	 * @return bool
	 */
	protected function set_topic_lock($topic_status, array &$qr_hidden_fields)
	{
		if ($topic_status == ITEM_LOCKED)
		{
			$qr_hidden_fields['lock_topic'] = 1;
		}
	}

	/**
	 * @param array $qr_hidden_fields
	 * @return bool
	 */
	protected function set_magic_urls(array &$qr_hidden_fields)
	{
		if (!$this->config['allow_post_links'])
		{
			$qr_hidden_fields['disable_magic_url'] = 1;
		}
	}
}
