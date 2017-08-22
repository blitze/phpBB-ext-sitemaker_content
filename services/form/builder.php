<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form;

use Cocur\Slugify\Slugify;

class builder
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\context */
	protected $template_context;

	/** @var \phpbb\user */
	protected $user;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/* @var \blitze\content\services\types */
	protected $types;

	/** @var \blitze\content\services\form\form */
	protected $form;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var bool */
	protected $req_approval = false;

	/** @var bool */
	protected $req_mod_input = false;

	/** @var bool */
	protected $user_is_mod = false;

	/** @var array */
	protected $content_fields = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth				Auth object
	 * @param \phpbb\event\dispatcher_interface			$phpbb_dispatcher	Event dispatcher object
	 * @param \phpbb\language\language					$language			Language object
	 * @param \phpbb\request\request_interface			$request			Request object
	 * @param \phpbb\template\context					$template_context	Template context object
	 * @param \phpbb\user								$user				User object
	 * @param \blitze\content\services\fields			$fields				Content fields object
	 * @param \blitze\content\services\types			$types				Content types object
	 * @param \blitze\content\services\form\form		$form				Form object
	 * @param string									$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string									$php_ext			php file extension
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\request\request_interface $request, \phpbb\template\context $template_context, \phpbb\user $user, \blitze\content\services\fields $fields, \blitze\content\services\types $types, \blitze\content\services\form\form $form, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->request = $request;
		$this->template_context = $template_context;
		$this->user = $user;
		$this->fields = $fields;
		$this->types = $types;
		$this->form = $form;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param int $forum_id
	 * @param int $topic_id
	 * @param string $mode
	 * @param bool $save_draft
	 * @return string|false
	 */
	public function init($forum_id, $topic_id, $mode, $save_draft)
	{
		if ($type = $this->types->get_forum_type($forum_id))
		{
			$this->language->add_lang('manager', 'blitze/content');

			$entity = $this->types->get_type($type);
			$fields_data = $entity->get_content_fields();

			/**
			 * Event to set the values for fields that are stored in the database, for purposes of displaying the form field
			 *
			 * @event blitze.content.builder.set_values
			 * @var int									topic_id		Current topic id
			 * @var array								fields_data		Array containing fields data, minus 'field_value' prop, which is what we are setting here
			 * @var \blitze\content\model\entity\type	entity			Content type entity
			 */
			$vars = array('topic_id', 'fields_data', 'entity');
			extract($this->phpbb_dispatcher->trigger_event('blitze.content.builder.set_values', compact($vars)));

			$this->content_fields = $fields_data;
			$this->user_is_mod = $this->auth->acl_get('m_', $entity->get_forum_id());
			$this->req_approval = $entity->get_req_approval();
			$this->mode = $this->request->variable('cp', ($this->user_is_mod && $mode !== 'post') ? 'mcp' : 'ucp');

			if ($save_draft && !$this->request->is_set('message'))
			{
				$this->request->overwrite('message', $this->generate_message());
			}
		}

		return $type;
	}

	/**
	 * @return string
	 */
	public function generate_message()
	{
		$fields_data = $this->form->get_submitted_data($this->content_fields, $this->req_mod_input, $this->mode);

		$message = '';
		foreach ($fields_data as $field => $value)
		{
			$value = is_array($value) ? join("\n", $value) : $value;
			$message .= '[tag=' . $field . ']' . $value . '[/tag]';
		}

		return $message;
	}

	/**
	 * @param int $topic_id
	 * @return void
	 */
	public function save_db_fields($topic_id)
	{
		$this->form->save_db_fields($topic_id, $this->content_fields);
	}

	/**
	 * @return array
	 */
	public function get_errors()
	{
		return $this->form->get_errors();
	}

	/**
	 * @return string
	 */
	public function get_cp_url()
	{
		return append_sid("{$this->phpbb_root_path}{$this->mode}.$this->php_ext", "i=-blitze-content-{$this->mode}-content_module&mode=content");
	}

	/**
	 * @param string $content_type
	 * @param array $topic_data
	 * @return string
	 */
	public function get_post_url($content_type, array $topic_data)
	{
		return $this->fields->get_topic_url($content_type, $topic_data);
	}

	/**
	 * @param string $content_type
	 * @param array $post_data
	 * @param string $view
	 * @return string
	 */
	public function get_content_view($content_type, array $post_data, $view)
	{
		$text = '';
		if ($entity = $this->types->get_type($content_type))
		{
			$get_tags = 'get_' . $view . '_tags';
			$get_template = 'get_' . $view . '_tpl';
	
			$this->fields->prepare_to_show($entity, array($post_data['topic_id']), $entity->$get_tags(), $entity->$get_template(), $view);
			$content = $this->fields->build_content(array_change_key_case($post_data, CASE_UPPER));
	
			$text =  $content['SEQ_DISPLAY'] ?: $content['CUSTOM_DISPLAY'];
		}
		return $text;
	}

	/**
	 * @param string $content_type
	 * @param array $post_data
	 * @return string
	 */
	public function generate_preview($content_type, array $post_data)
	{
		$dataref = $this->template_context->get_data_ref();
		$post_data['MESSAGE'] = $dataref['.'][0]['PREVIEW_MESSAGE'];

		return $this->get_content_view($content_type, $post_data, 'detail');
	}

	/**
	 * @param array $sql_data
	 * @return void
	 */
	public function modify_posting_data(array &$sql_data)
	{
		$slugify = new Slugify();
		$sql_data['topic_slug'] = $slugify->slugify($sql_data['topic_title']);
		$sql_data['req_mod_input'] = $this->req_mod_input;

		if ($this->mode === 'mcp')
		{
			$topic_time = $this->request->variable('topic_time', 0);
			$publish_on = $this->request->variable('publish_on', '');

			$posted_on = $this->user->format_date($topic_time, 'm/d/Y H:i');

			if ($publish_on !== $posted_on)
			{
				$sql_data['topic_time'] = strtotime($publish_on);
			}
		}
	}

	/**
	 * @param int $topic_id
	 * @param array $post_data
	 * @param array $page_data
	 * @return string
	 */
	public function generate_form($topic_id, array &$post_data, array $page_data = array())
	{
		$this->set_field_values($post_data['post_text']);

		$this->form->create('postform', 'posting')
			->add('cp', 'hidden', array('field_value' => $this->mode));

		foreach ($this->content_fields as $field => $field_data)
		{
			if ($field_data['field_type'] === 'textarea')
			{
				$field_data += $page_data;
			}
			$this->add_field($field, $field_data, $topic_id);
		}
		$this->add_moderator_fields($post_data);

		return $this->form->get_form(false);
	}

	/**
	 * @param string $field
	 * @param array $field_data
	 * @param int $topic_id
	 * @return void
	 */
	protected function add_field($field, array $field_data, $topic_id)
	{
		if (!$field_data['field_mod_only'] || $this->mode === 'mcp')
		{
			$this->form->add($field, $field_data['field_type'], $field_data, $topic_id);
		}
		else if (!empty($field_data['field_value']))
		{
			$this->form->add($field, 'hidden', $field_data, $topic_id);
		}
	}

	/**
	 * @param array $post_data
	 * @return void
	 */
	protected function add_moderator_fields(array $post_data)
	{
		if ($this->mode === 'mcp')
		{
			$this->form->add('topic_time', 'hidden', array('field_value' => $post_data['topic_time']))
				->add('publish_on', 'datetime', array(
					'field_label'	=> $this->language->lang('CONTENT_POST_DATE'),
					'field_value'	=> $this->user->format_date($post_data['topic_time'], 'm/d/Y H:i'),
					'field_props'	=> array(
						'min_date'	=> 0,
					),
				))
				->add('force_status', 'radio', array(
					'field_label'	=> 'FORCE_STATUS',
					'field_value'	=> 'NO',
					'field_props'	=> array(
						'vertical'		=> true,
						'options' 		=> array(
							''					=> 'NO',
							ITEM_UNAPPROVED		=> 'STATUS_DISAPPROVE',
							ITEM_APPROVED		=> 'STATUS_APPROVE',
							ITEM_REAPPROVE		=> 'STATUS_REAPPROVE',
						)
					)
				)
			);
		}
	}

	/**
	 * @param array $data
	 * @return void
	 */
	public function force_visibility(array &$data)
	{
		if ($this->mode === 'mcp')
		{
			if ('' !== $force_status = $this->request->variable('force_status', ''))
			{
				$data['force_approved_state'] = $force_status;
			}
		}
		else
		{
			if ($this->force_state())
			{
				$data['force_approved_state'] = (empty($data['topic_id'])) ? ITEM_UNAPPROVED : ITEM_REAPPROVE;
			}
		}
	}

	/**
	 * @return bool
	 */
	protected function force_state()
	{
		return ($this->req_approval || $this->req_mod_input);
	}

	/**
	 * @param string $post_text
	 * @return void
	 */
	protected function set_field_values($post_text)
	{
		$fields_data = $this->get_fields_data_from_post($post_text, array_keys($this->content_fields));

		foreach ($fields_data as $field => $value)
		{
			if (isset($this->content_fields[$field]))
			{
				$this->content_fields[$field]['field_value'] = $value;
			}
		}
	}

	/**
	 * Get fields data from post
	 *
	 * @param string $post_text
	 * @param array $fields
	 * @return array
	 */
	protected function get_fields_data_from_post($post_text, array $fields)
	{
		$fields_data = array();
		$find_tags = join('|', $fields);

		if (preg_match_all("/\[tag=($find_tags)\](.*?)\[\/tag]/s", $post_text, $matches))
		{
			$fields_data = array_combine($matches[1], $matches[2]);
		}

		return $fields_data;
	}
}
