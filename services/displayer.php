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

	/** @var \primetime\cotent\services\comments */
	protected $comments;

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
	 * @param \primetime\cotent\services\comments			$comments				Comments object
	 * @param string										$root_path				phpBB root path
	 * @param string										$fields_table			Name of content fields database table
	 * @param string										$types_table			Name of content types database table
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, Container $phpbb_container, \phpbb\template\template $template, \phpbb\user $user, \primetime\content\services\comments $comments, $root_path, $fields_table, $types_table)
	{
		parent::__construct($cache, $config, $db, $fields_table, $types_table);

		$this->auth					= $auth;
		$this->config				= $config;
		$this->content_visibility	= $content_visibility;
		$this->helper				= $helper;
		$this->phpbb_container		= $phpbb_container;
		$this->template				= $template;
		$this->user					= $user;
		$this->comments				= $comments;
		$this->root_path			= $root_path;
	}

	/**
	 * Set type data needed to display posts
	 */
	public function prepare_to_show($type, $view, $fields, $custom_tpl = '', $tpl_name = '', $force_max_chars = '')
	{
		$form = $this->phpbb_container->get('primetime.content.form.builder');

		$this->tags = array();
		$this->type_fields = array();
		$this->form_fields = null;
		$this->tpl_name = '';
		$this->type_data = $this->get_type($type);

		$fields_data = $this->type_data['content_fields'];

		if ($this->type_data['allow_comments'])
		{
			$this->template->assign_var('S_COMMENTS', true);
			$this->allow_comments = true;
		}

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
				if ($force_max_chars)
				{
					$row['max_chars'] = $force_max_chars;
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
	public function show($type, $topic_title, $topic_data, $post_data, $user_cache, $topic_tracking_info = array(), $page = 1)
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
		$post_unread = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

		$topic_url = $this->helper->route('primetime_content_show', array(
			'type'		=> $type,
			'topic_id'	=> $topic_id,
			'slug'		=> $topic_data['topic_slug']
		));

		$topic_row = array(
			'MINI_POST_IMG'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),
			'TOPIC_AUTHOR'			=> $user_cache['author_username'],
			'TOPIC_AUTHOR_COLOUR'	=> $user_cache['author_colour'],
			'TOPIC_AUTHOR_FULL'		=> $user_cache['author_full'],
			'TOPIC_AUTHOR_URL'		=> $user_cache['author_profile'],
			'TOPIC_AUTHOR_AVATAR'	=> $user_cache['avatar'],

			'S_UNREAD_POST'			=> $post_unread,

			'TOPIC_TITLE'			=> $topic_title,
			'TOPIC_COMMENTS'		=> ($this->allow_comments) ? $this->comments->count($topic_data) : '',
			'TOPIC_DATE'			=> $this->user->format_date($row['topic_time']),
			'TOPIC_URL'				=> $topic_url,
		);

		$fields_data = array();
		foreach ($this->type_fields as $field_name => $row)
		{
			$field_type		= $row['field_type'];
			$field_value	= &$post_field_data[$field_name];

			$field_contents	= $this->form_fields[$field_type]->display_field($field_value, $row, $this->view, $topic_id);

			if (!$field_contents)
			{
				continue;
			}
			else if ($field_type == 'textarea' && $row['size'] == 'large' && strpos($field_value, '<!-- PAGE') !== false)
			{
				if (preg_match_all("#<!-- PAGE(.*?)? -->(.*?)<!-- ENDPAGE -->#s", $field_value, $matches))
				{
					if (sizeof($matches[2]))
					{
						$total_pages = sizeof($matches[2]);
						$start = (($page > $total_pages) ? $total_pages - 1 : (($page < 1) ? 1 : $page)) - 1;
						$field_contents = $matches[2][$start];

						// Generate pagination
						if ($this->view == 'detail')
						{
							$this->phpbb_container->get('pagination')->generate_template_pagination(
								array(
									'routes' => array(
										'primetime_content_show',
										'primetime_content_show_page',
									),
									'params' => array(
										'type'		=> $type,
										'topic_id'	=> $topic_id,
										'slug'		=> $topic_data['topic_slug']
									),
								),
								'page', 'page', $total_pages, 1, $start);

							// Generate TOC
							if (sizeof(array_filter($matches[1])))
							{
								foreach ($matches[1] as $pg => $title)
								{
									$title = ($title) ? $title : $this->user->lang['CONTENT_TOC_UNTITLED'];
									$this->template->assign_block_vars('toc', array(
										'TITLE'		=> $title,
										'S_PAGE'	=> ($pg == $start) ? true : false,
										'U_VIEW'	=> $topic_url . (($pg > 0) ? '/page/' . ($pg + 1) : ''),
									));
								}
							}

							// When Previewing
							if (!empty($post_data['preview']))
							{
								for ($i = 1, $size = sizeof($matches[2]); $i < $size; $i++)
								{
									$this->template->assign_block_vars('pages', array(
										'CONTENT'	=> $matches[2][$i],
										'PAGE'		=> $i + 1,
									));
								}
							}

							// Hide all other fields if we're looking at page 2+
							if ($page > 1)
							{
								$fields_data = array(
									$field_name	=> $field_contents
								);
								break;
							}
						}
					}
				}
			}

			$fields_data[$field_name] = '<div class="field-label ' . $label_class[$row['field_' . $this->view . '_ldisp']] . '">' . $row['field_label'] . ': </div>' . $field_contents;
		}

		$topic_row = array_change_key_case(array_merge($topic_row, $fields_data), CASE_UPPER);

		if ($this->tpl_name)
		{
			$topic_row['CUSTOM_DISPLAY'] = $this->twig->render($this->tpl_name, $topic_row);
		}
		else
		{
			$topic_row['SEQ_DISPLAY'] = join('', $fields_data);
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
