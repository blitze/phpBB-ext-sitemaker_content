<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\services;

use Symfony\Component\DependencyInjection\Container;

class displayer extends types
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var Container */
	protected $phpbb_container;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var field object collection */
	protected $form_fields;

	/** @var array */
	protected $tags;

	/** @var array */
	protected $type_data;

	/** @var array */
	protected $type_fields;

	/** @var string */
	protected $tpl_name = '';

	/** @var string */
	protected $view = '';

	/**
	 * Construct
	 *
	 * @param \phpbb\auth\auth								$auth					Auth object
	 * @param \phpbb\cache\service							$cache					Cache object
	 * @param \phpbb\config\db								$config					Config object
	 * @param \phpbb\content_visibility						$content_visibility		Template context
	 * @param \phpbb\template\context						$context				Template context
	 * @param \phpbb\db\driver\driver_interface				$db						Database connection
	 * @param \phpbb\controller\helper						$helper					Helper object
	 * @param Container										$phpbb_container		Service container
	 * @param \phpbb\template\template						$template				Template object
	 * @param \phpbb\user									$user					User object
	 * @param string										$root_path				phpBB root path
	 * @param string										$fields_table			Name of content fields database table
	 * @param string										$types_table			Name of content types database table
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, Container $phpbb_container, \phpbb\template\template $template, \phpbb\user $user, $root_path, $fields_table, $types_table)
	{
		parent::__construct($cache, $config, $db, $fields_table, $types_table);

		$this->auth					= $auth;
		$this->config				= $config;
		$this->content_visibility	= $content_visibility;
		$this->helper				= $helper;
		$this->phpbb_container		= $phpbb_container;
		$this->template				= $template;
		$this->user					= $user;
		$this->root_path			= $root_path;
	}

	/**
	 * Set type data needed to display posts
	 */
	public function prepare_to_show($type, $view, $fields, $custom_tpl = '', $tpl_name = '', $max_chars = '')
	{
		$form = $this->phpbb_container->get('primetime.content.form.builder');

		$this->tags = array();
		$this->type_fields = array();
		$this->form_fields = null;
		$this->tpl_name = '';
		$this->type_data = $this->get_type($type);

		$fields_data = $this->type_data['content_fields'];

		if (!empty($custom_tpl))
		{
			$this->twig		= $this->get_twig();
			$this->tpl_name	= ($tpl_name) ? $tpl_name : $type . '_' . $view;
		}

		$this->view = ($view == 'summary' || $view == 'detail') ? $view : 'summary';

		if (sizeof($fields))
		{
			$this->form_fields = array_intersect_key($form->get_form_fields(), array_flip($fields));
		}

		foreach ($fields_data as $field => $row)
		{
			if (isset($this->form_fields[$row['field_type']]))
			{
				if ($max_chars)
				{
					$row['max_chars'] = $max_chars;
				}
				$this->tags[] = $field;
				$this->type_fields[$field] = $row;
			}
		}
		unset($fields, $fields_data);
	}

	/**
	 * Get post
	 */
	public function show($type, $topic_title, $topic_data, $post_data, $user_cache, $topic_tracking_info = array())
	{
		$row = $topic_data;
		$forum_id = $row['forum_id'];
		$topic_id = $row['topic_id'];
		$post_id = $post_data['post_id'];

		/*$update_count = array();
		if (!empty($attachments[$post_id]))
		{
			parse_attachments($forum_id, $post_data['post_text'], $attachments[$post_id], $update_count);
		}*/

		$label_class = array('label-hidden', 'label-inline', 'label-newline');
		$post_field_data = $this->get_fields_data_from_post($post_data['post_text'], $this->tags);
		$replies = $this->content_visibility->get_count('topic_posts', $row, $forum_id) - 1;
		$post_unread = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

		$topic_url = $this->helper->route('primetime_content_show', array(
			'type'		=> $type,
			'topic_id'	=> $topic_id,
			'slug'		=> $topic_data['topic_slug']
		));

		$topic_row = array(
			'MINI_POST_IMG'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),
			'TOPIC_AUTHOR'			=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_URL'		=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_AVATAR'	=> $user_cache['avatar'],

			'S_UNREAD_POST'			=> $post_unread,

			'TOPIC_TITLE'			=> $topic_title,
			'TOPIC_COMMENTS'		=> $this->content_visibility->get_count('topic_posts', $row, $row['forum_id']) - 1,
			'TOPIC_DATE'			=> $this->user->format_date($row['topic_time']),
			'TOPIC_URL'				=> $topic_url,
		);

		$fields_data = array();
		foreach ($this->type_fields as $fname => $row)
		{
			$ftype	= $row['field_type'];
			$fvalue	= &$post_field_data[$fname];
			$fdisp	= $this->form_fields[$ftype]->display_field($fvalue, $row, $this->view, $topic_id);

			if (!$fdisp)
			{
				continue;
			}

			$fclass	= $label_class[$row['field_' . $this->view . '_ldisp']];
			$fdisp	= '<div class="field-label ' . $fclass . '">' . $row['field_label'] . ': </div>' . $fdisp;

			$fields_data[$fname] = $fdisp;
		}

		$topic_row = array_change_key_case(array_merge($topic_row, $fields_data), CASE_UPPER);

		if ($this->tpl_name)
		{
			$topic_row['CUSTOM_DISPLAY'] = $this->twig->render($this->tpl_name, $topic_row);
		}
		else
		{
			$topic_row['SEQ_DISPLAY'] = join('<br /><br />', $fields_data);
		}
		unset($fields_data, $post_field_data, $topic_data, $post_data, $row);

		return $topic_row;
	}

	public function get_twig()
	{
		$twig = new \Twig_Environment(
			$this->phpbb_container->get('primetime.content.loader'),
			array(
				'cache'			=> $this->root_path . 'cache/twig/',
				'debug'			=> defined('DEBUG'),
				'auto_reload'	=> (bool) $this->config['load_tplcompile'],
				'autoescape'	=> false,
			)
		);

		$twig->addExtension(
			new \phpbb\template\twig\extension(
				$this->phpbb_container->get('template_context'),
				$this->user
			)
		);

		$lexer = new \phpbb\template\twig\lexer($twig);
		$twig->setLexer($lexer);

		return $twig;
	}
}
