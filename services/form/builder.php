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

	/** @var string */
	protected $mode = '';

	/** @var int */
	protected $topic_time = 0;

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
	 * @return array
	 */
	public function init($forum_id, $topic_id, $mode, $save_draft)
	{
		$content_langname = '';
		if ($type = $this->types->get_forum_type($forum_id))
		{
			$this->language->add_lang('posting', 'blitze/content');

			/** @var \blitze\content\model\entity\type $entity */
			$entity = $this->types->get_type($type, true);
			$content_langname = $entity->get_content_langname();
			$fields_data = $entity->get_content_fields();

			/**
			 * Event to set the values for fields that are stored in the database, for purposes of displaying the form field
			 *
			 * @event blitze.content.builder.set_field_values
			 * @var int									topic_id		Current topic id
			 * @var array								fields_data		Array containing fields data, minus 'field_value' prop, which is what we are setting here
			 * @var \blitze\content\model\entity\type	entity			Content type entity
			 */
			$vars = array('topic_id', 'fields_data', 'entity');
			extract($this->phpbb_dispatcher->trigger_event('blitze.content.builder.set_field_values', compact($vars)));

			$this->content_fields = $fields_data;
			$this->user_is_mod = $this->auth->acl_get('m_', $entity->get_forum_id());
			$this->req_approval = $entity->get_req_approval();
			$this->mode = $this->request->variable('cp', ($this->user_is_mod && $mode !== 'post') ? 'mcp' : 'ucp');

			$this->form->create('postform', 'posting')
				->add('cp', 'hidden', array('field_value' => $this->mode))
				->add('redirect', 'hidden', array('field_value' => $this->get_redirect_url()));

			if ($save_draft && !$this->request->is_set('message'))
			{
				$this->request->overwrite('message', $this->generate_message());
			}
		}

		return array($type, $content_langname);
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
			$message .= '[smcf=' . $field . ']' . $value . '[/smcf]';
		}

		return $message;
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
	public function get_redirect_url()
	{
		return $this->request->variable('redirect', '');
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
			$fields_accessor = 'get_' . $view . '_fields';
			$template_accessor = 'get_' . $view . '_tpl';

			// we do this to ensure topic_id keys exists when previewing a new topic
			$post_data += array('topic_id' => 0);

			$this->fields->prepare_to_show($entity, array($post_data['topic_id']), $entity->$fields_accessor(), $entity->$template_accessor(), $view);
			$this->fields->set_view_mode('preview');

			$content = $this->fields->build_content(array_change_key_case($post_data, CASE_UPPER));

			$text =  isset($content['CUSTOM_DISPLAY']) ? $content['CUSTOM_DISPLAY'] : join('', $content['FIELDS']['all']);
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
		$post_data['MESSAGE'] = $this->template_context->get_root_ref()['PREVIEW_MESSAGE'];

		return $this->get_content_view($content_type, $post_data, 'detail');
	}

	/**
	 * @param array $sql_data
	 * @return void
	 */
	public function modify_sql_data(array &$sql_data)
	{
		$slugify = new Slugify();
		$sql_data['topic_slug'] = $slugify->slugify($sql_data['topic_title']);
		$sql_data['req_mod_input'] = $this->req_mod_input;
	}

	/**
	 * @param array $topic_data
	 * @return void
	 */
	public function save_db_fields(array $topic_data)
	{
		$this->form->save_db_fields($topic_data, $this->content_fields);
	}

	/**
	 * @param int $topic_id
	 * @param array $post_data
	 * @param array $page_data
	 * @return void
	 */
	public function generate_form($topic_id, array $post_data, array $page_data = array())
	{
		$this->set_field_values($post_data['post_text']);

		foreach ($this->content_fields as $field => $field_data)
		{
			if ($field_data['field_type'] === 'textarea')
			{
				$field_data += $page_data;
			}
			$this->add_field($field, $field_data, $topic_id);
		}
	}

	/**
	 * @return string
	 */
	public function get_form()
	{
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
	 * @param string $mode
	 * @param array $data
	 * @return void
	 */
	public function force_visibility($mode, array &$data)
	{
		if ($this->mode === 'mcp')
		{
			if ('-1' !== $visibility = $this->request->variable('force_status', ''))
			{
				$data['force_visibility'] = $visibility;
			}
		}
		else if ($this->force_state())
		{
			$data['force_visibility'] = ($mode == 'edit_first_post') ? ITEM_REAPPROVE : ITEM_UNAPPROVED;
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
		$find_fields = join('|', $fields);

		if (preg_match_all("/\[smcf=($find_fields)\](.*?)\[\/smcf]/s", $post_text, $matches))
		{
			$fields_data = array_combine($matches[1], $matches[2]);
		}

		return $fields_data;
	}

	/**
	 * @param int $visibility
	 * @return array
	 */
	protected function get_moderator_options($visibility)
	{
		$options = array(
			'-1' => 'NO',
		);

		if ($visibility == ITEM_APPROVED)
		{
			$options[ITEM_REAPPROVE] = 'CONTENT_STATUS_REAPPROVE';
		}
		else
		{
			$options[ITEM_UNAPPROVED]	= 'CONTENT_STATUS_DISAPPROVE';
			$options[ITEM_APPROVED]		= 'CONTENT_STATUS_APPROVE';
		}

		return $options;
	}
}
