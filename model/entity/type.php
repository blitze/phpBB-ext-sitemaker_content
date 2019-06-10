<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\model\entity;

use blitze\sitemaker\model\base_entity;

/**
 * @method integer get_content_id()
 * @method object set_forum_id($forum_id)
 * @method integer get_forum_id()
 * @method string get_content_name()
 * @method string get_content_langname()
 * @method object set_content_enabled($enabled)
 * @method boolean get_content_enabled()
 * @method object set_content_colour($color)
 * @method string get_content_colour()
 * @method object set_content_desc_bitfield($bitfield)
 * @method string get_content_desc_bitfield()
 * @method object set_content_desc_options($options)
 * @method integer get_content_desc_options()
 * @method object set_content_desc_uid($uid)
 * @method string get_content_desc_uid()
 * @method object set_content_view($view)
 * @method string get_content_view()
 * @method object set_comments($comments)
 * @method string get_comments()
 * @method object set_req_approval($require_approval)
 * @method boolean get_req_approval()
 * @method object set_allow_views($allow_views)
 * @method boolean get_allow_views()
 * @method object set_show_pagination($show_pagination)
 * @method boolean get_show_pagination()
 * @method object set_index_show_desc($index_show_desc)
 * @method boolean get_index_show_desc()
 * @method integer get_items_per_page()
 * @method object set_summary_tpl($summary_tpl)
 * @method object set_detail_tpl($detail_tpl)
 * @method object set_last_modified($timestamp)
 * @method integer get_last_modified()
 * @method array get_content_fields()
 * @method object set_field_types($field_types)
 * @method array get_field_types()
 * @method object set_summary_fields($summary_fields)
 * @method array get_summary_fields()
 * @method object set_detail_fields($detail_fields)
 * @method array get_detail_fields()
 *
 */
final class type extends base_entity
{
	/** @var integer */
	protected $content_id;

	/** @var integer */
	protected $forum_id;

	/** @var string */
	protected $content_name;

	/** @var string */
	protected $content_langname;

	/** @var boolean */
	protected $content_enabled = true;

	/** @var string */
	protected $content_colour = '';

	/** @var string */
	protected $content_desc = '';

	/** @var string */
	protected $content_desc_bitfield = '';

	/** @var integer */
	protected $content_desc_options = 7;

	/** @var string */
	protected $content_desc_uid = '';

	/** @var string */
	protected $content_view;

	/** @var string */
	protected $content_view_settings = '';

	/** @var string */
	protected $comments = '';

	/** @var string */
	protected $comments_settings = '';

	/** @var boolean */
	protected $req_approval = false;

	/** @var boolean */
	protected $allow_views = true;

	/** @var boolean */
	protected $show_pagination = true;

	/** @var boolean */
	protected $index_show_desc = false;

	/** @var integer */
	protected $items_per_page = 10;

	/** @var string */
	protected $summary_tpl = '';

	/** @var string */
	protected $detail_tpl = '';

	/** @var integer */
	protected $last_modified = 0;

	/** @var string */
	protected $topic_blocks = array();

	/** @var array */
	protected $content_fields = array();

	/** @var array */
	protected $field_types = array();

	/** @var array */
	protected $summary_fields = array();

	/** @var array */
	protected $detail_fields = array();

	/** @var array */
	protected $required_fields = array('forum_id', 'content_name', 'content_langname', 'content_view');

	/** @var array */
	protected $db_fields = array(
		'forum_id',
		'content_name',
		'content_langname',
		'content_enabled',
		'content_colour',
		'content_desc',
		'content_desc_bitfield',
		'content_desc_options',
		'content_desc_uid',
		'content_view',
		'content_view_settings',
		'comments',
		'comments_settings',
		'req_approval',
		'allow_views',
		'show_pagination',
		'index_show_desc',
		'items_per_page',
		'summary_tpl',
		'detail_tpl',
		'last_modified',
		'topic_blocks',
	);

	/**
	 * Set content ID
	 * @param int $content_id
	 * @return $this
	 */
	public function set_content_id($content_id)
	{
		if (!$this->content_id)
		{
			$this->content_id = (int) $content_id;
		}
		return $this;
	}

	/**
	 * Set content name
	 * @param string $name
	 * @return $this
	 */
	public function set_content_name($name)
	{
		$this->content_name = str_replace(' ', '_', strtolower(trim($name)));
		$this->content_colour = substr(md5($this->content_name), 0, 6);
		return $this;
	}

	/**
	 * Set content display name
	 * @param string $langname
	 * @return $this
	 */
	public function set_content_langname($langname)
	{
		$this->content_langname = ucwords($langname);
		return $this;
	}

	/**
	 * @param string $desc
	 * @param string $mode
	 * @return $this
	 */
	public function set_content_desc($desc, $mode = '')
	{
		$this->content_desc = $desc;

		if ($this->content_desc && $mode === 'storage')
		{
			generate_text_for_storage($this->content_desc, $this->content_desc_uid, $this->content_desc_bitfield, $this->content_desc_options, true, true, true);
		}
		return $this;
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	public function get_content_desc($mode = 'display')
	{
		if ($mode === 'edit')
		{
			$data = generate_text_for_edit($this->content_desc, $this->content_desc_uid, $this->content_desc_options);
			return $data['text'];
		}
		else
		{
			$parse_flags = ($this->content_desc_bitfield ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			return generate_text_for_display($this->content_desc, $this->content_desc_uid, $this->content_desc_bitfield, $parse_flags);
		}
	}

	/**
	 * Set content view settings
	 * @param array|string $settings
	 * @return $this
	 */
	public function set_content_view_settings($settings)
	{
		$this->set_array_field('content_view_settings', $settings);
		return $this;
	}

	/**
	 * Get content view settings
	 * @return array
	 */
	public function get_content_view_settings()
	{
		return $this->get_array_field('content_view_settings');
	}

	/**
	 * Set comment settings
	 * @param array|string $settings
	 * @return $this
	 */
	public function set_comments_settings($settings)
	{
		$this->set_array_field('comments_settings', $settings);
		return $this;
	}

	/**
	 * Get comment type settings
	 * @return array
	 */
	public function get_comments_settings()
	{
		return $this->get_array_field('comments_settings');
	}

	/**
	 * Set Items per page. Must be greater than zero
	 * @param int $items_per_page
	 * @return $this
	 */
	public function set_items_per_page($items_per_page)
	{
		$this->items_per_page = ($items_per_page) ? $items_per_page : 1;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_summary_tpl()
	{
		return htmlspecialchars_decode($this->summary_tpl);
	}

	/**
	 * @return string
	 */
	public function get_detail_tpl()
	{
		return htmlspecialchars_decode($this->detail_tpl);
	}

	/**
	 * @param array $content_fields
	 */
	public function set_content_fields(array $content_fields)
	{
		$fields = strtoupper(join('|', array_keys($content_fields)));

		$field_types = $summary_fields = $detail_fields = array();
		$this->parse_content_fields($content_fields, $field_types, $summary_fields, $detail_fields);

		$summary_fields = $this->get_template_fields($summary_fields, $this->summary_tpl, $fields);
		$detail_fields = $this->get_template_fields($detail_fields, $this->detail_tpl, $fields);

		$this->summary_fields	= array_intersect_key($field_types, array_flip($summary_fields));
		$this->detail_fields	= array_intersect_key($field_types, array_flip($detail_fields));

		$this->content_fields	= $content_fields;
		$this->field_types		= $field_types;
	}

	/**
	 * Set topic blocks + settings
	 * @param array|string $settings
	 * @return $this
	 */
	public function set_topic_blocks($settings)
	{
		$this->set_array_field('topic_blocks', $settings);
		return $this;
	}

	/**
	 * Get topic blocks + settings
	 * @return array
	 */
	public function get_topic_blocks()
	{
		return $this->get_array_field('topic_blocks');
	}

	/**
	 * @param array $content_fields
	 * @param array $field_types
	 * @param array $summary_fields
	 * @param array $detail_fields
	 * @return void
	 */
	private function parse_content_fields(array $content_fields, array &$field_types, array &$summary_fields, array &$detail_fields)
	{
		foreach ($content_fields as $field => $data)
		{
			$field_types[$field] = $data['field_type'];

			if ($data['field_summary_show'])
			{
				$summary_fields[] = $field;
			}

			if ($data['field_detail_show'])
			{
				$detail_fields[] = $field;
			}
		}
	}

	/**
	 * @param array $view_fields
	 * @param string $template
	 * @param string $fields
	 * @return array
	 */
	private function get_template_fields(array $view_fields, $template, $fields)
	{
		if ($template)
		{
			preg_match_all("/\{($fields)\}|\{\{\s($fields)\s\}\}/", $template, $view_fields);
			$view_fields = array_map('strtolower', array_pop($view_fields));
		}

		return $view_fields;
	}
}
