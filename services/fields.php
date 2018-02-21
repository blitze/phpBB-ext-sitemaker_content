<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services;

class fields extends topic
{
	/** @var \blitze\content\services\comments\comments_interface */
	protected $comments;

	/** @var \blitze\content\services\form\fields_factory */
	protected $fields_factory;

	/** @var array */
	protected $form_fields;

	/** @var string */
	protected $content_type;

	/** @var array */
	protected $content_fields;

	/** @var string */
	protected $board_url = '';

	/** @var string */
	protected $tpl_name = '';

	/** @var string */
	protected $display_mode = '';

	/** @var string */
	protected $view_mode = '';

	/** @var array */
	protected $db_fields = array();

	/** @var array */
	protected $label = array('', 'label-hidden', 'label-inline', 'label-newline');

	/**
	 * Construct
	 *
	 * @param \phpbb\config\db										$config					Config object
	 * @param \phpbb\controller\helper								$controller_helper		Controller Helper object
	 * @param \phpbb\event\dispatcher_interface						$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language								$language				Language object
	 * @param \phpbb\template\template								$template				Template object
	 * @param \phpbb\user											$user					User object
	 * @param \blitze\content\services\helper						$helper					Content helper object
	 * @param \blitze\content\services\comments\comments_interface	$comments				Comments object
	 * @param \blitze\content\services\form\fields_factory			$fields_factory			Form fields factory
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\controller\helper $controller_helper, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\helper $helper, \blitze\content\services\comments\comments_interface $comments, \blitze\content\services\form\fields_factory $fields_factory)
	{
		parent::__construct($config, $controller_helper, $phpbb_dispatcher, $language, $template, $user, $helper);

		$this->comments = $comments;
		$this->fields_factory = $fields_factory;
	}

	/**
	 * Set type data needed to display topics. This should only run once
	 *
	 * @param \blitze\content\model\entity\type $entity
	 * @param array $topic_ids
	 * @param array $view_mode_fields	array of form array([field_name] => [field_type])
	 * @param string $custom_tpl
	 * @param string $view_mode
	 * @param string $tpl_name
	 * @return void
	 */
	public function prepare_to_show(\blitze\content\model\entity\type $entity, array $topic_ids, array $view_mode_fields, $custom_tpl, $view_mode, $tpl_name = null)
	{
		$this->reset();
		$db_fields = array_fill_keys($topic_ids, array());

		/**
		 * Event to set the values for fields that are stored in the database as opposed to post text e.g. categories
		 * We do this here so that we can get all values with one query instead of multiple queries for each field for each topic
		 *
		 * @event blitze.content.fields.set_values
		 * @var string								view_mode			The current view mode (summary|detail|block)
		 * @var	array								view_mode_fields	Array containing fields for current view_mode
		 * @var \blitze\content\model\entity\type	entity				Content type entity
		 * @var array								db_fields			This array allows extensions that provide fields to list field values for current topic ids.
		 *																Ex. array([topic_id] => array([field_name] => [field_value]))
		 */
		$vars = array('view_mode', 'view_mode_fields', 'entity', 'db_fields');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.fields.set_values', compact($vars)));

		$this->board_url = generate_board_url(true);
		$this->display_mode = $view_mode;
		$this->tpl_name	= ($custom_tpl) ? $tpl_name ?: $this->content_type . '_' . $view_mode : '';
		$this->view_mode = (in_array($view_mode, array('summary', 'detail'))) ? $view_mode : 'summary';
		$this->db_fields = $db_fields;

		$this->content_type = $entity->get_content_name();
		$this->set_form_fields($view_mode_fields);
		$this->set_content_fields($view_mode_fields, $entity->get_content_fields());
	}

	/**
	 * @param string $type
	 * @param array $topic_data
	 * @param array $post_data
	 * @param array $users_cache
	 * @param array $attachments
	 * @param array $update_count
	 * @param array $topic_tracking_info
	 * @param array $topic_data_overwrite
	 * @param string $mode
	 * @return array
	 */
	public function show($type, array $topic_data, array $post_data, array $users_cache, array &$attachments, array &$update_count, array $topic_tracking_info, array $topic_data_overwrite = array(), $mode = '')
	{
		$callable = 'get_' . $this->view_mode . '_template_data';
		$tpl_data = array_merge(array(
				'TOPIC_COMMENTS'	=> $this->comments->count($topic_data),
				'S_USER_LOGGED_IN'	=> $this->user->data['is_registered'],
			),
			$this->{$callable}($type, $topic_data, $post_data, $users_cache, $attachments, $topic_tracking_info, $update_count, $mode),
			$topic_data_overwrite
		);

		$tpl_data['PERMA_LINK'] = $this->board_url . parse_url($tpl_data['TOPIC_URL'], PHP_URL_PATH);

		return $this->build_content($tpl_data);
	}

	/**
	 * @param array $tpl_data
	 * @return array
	 */
	public function build_content(array $tpl_data)
	{
		$fields_data = $this->get_fields_data_for_display($tpl_data);

		if ($this->tpl_name)
		{
			$this->template->assign_vars(array_change_key_case(array_merge($tpl_data, $fields_data['all']), CASE_UPPER));
			$this->template->set_filenames(array('content' => $this->tpl_name));
			$tpl_data['CUSTOM_DISPLAY'] = $this->template->assign_display('content');
		}
		else
		{
			$tpl_data['FIELDS'] = $fields_data;
		}
		unset($fields_data);

		return $tpl_data;
	}

	/**
	 * @param array $view_mode_fields	array of form array([field_name] => [field_type])
	 * @param array $fields_data
	 * @return void
	 */
	public function set_content_fields(array $view_mode_fields, array $fields_data)
	{
		foreach ($view_mode_fields as $name => $field_type)
		{
			if (isset($this->form_fields[$field_type]))
			{
				$this->content_fields[$name] = $fields_data[$name];
			}
		}
	}

	/**
	 * @param array $view_mode_fields	array of form array([field_name] => [field_type])
	 * @return void
	 */
	public function set_form_fields(array $view_mode_fields)
	{
		$this->form_fields = array_intersect_key($this->fields_factory->get_all(), array_flip($view_mode_fields));
	}

	/**
	 * @param string $type
	 * @return void
	 */
	public function set_content_type($type)
	{
		$this->content_type = $type;
	}

	/**
	 * @param array $tpl_data
	 * @return array
	 */
	protected function get_fields_data_for_display(array &$tpl_data)
	{
		$field_values = $this->get_field_values($tpl_data);
		$display_data = array_fill_keys(array('all', 'above', 'body', 'inline', 'footer'), array());

		foreach ($this->content_fields as $field_name => $field_data)
		{
			$field_type = $field_data['field_type'];
			$field_data['content_type'] = $this->content_type;
			$field_data['field_props'] = array_replace_recursive($this->form_fields[$field_type]->get_default_props(), $field_data['field_props']);
			$field_data['field_value'] = &$field_values[$field_name];

			$field_contents	= $this->form_fields[$field_type]->display_field($field_data, $tpl_data, $this->display_mode);

			// this essentially hides other fields if the field returns an array
			if (is_array($field_contents))
			{
				$display_data['all'] = $field_contents;
				$display_data[$field_data['field_' . $this->view_mode . '_show']] = $field_contents;
				break;
			}

			if (!empty($field_contents))
			{
				$field = $this->get_field_label($field_data['field_' . $this->view_mode . '_ldisp'], $field_data['field_label']) . $field_contents;
				$display_data['all'][$field_name] = $field;
				$display_data[$field_data['field_' . $this->view_mode . '_show']][$field_name] = $field;
			}
		}

		return $display_data;
	}

	/**
	 * @param int $label_type
	 * @param string $label_text
	 * @return string
	 */
	protected function get_field_label($label_type, $label_text)
	{
		$html = '';
		if ($label_type)
		{
			$html = '<div class="field-label ' . $this->label[$label_type] . '">' . $label_text . $this->language->lang('COLON') . ' </div>';
		}
		return $html;
	}

	/**
	 * @param array $tpl_data
	 * @return array
	 */
	protected function get_field_values(array &$tpl_data)
	{
		$message = $tpl_data['MESSAGE'];
		unset($tpl_data['MESSAGE']);

		return array_merge(
			$this->db_fields[$tpl_data['TOPIC_ID']] ?: array(),
			$this->get_fields_data_from_post($message)
		);
	}

	/**
	 * @param string $post_text
	 * @return array
	 */
	protected function get_fields_data_from_post($post_text)
	{
		$fields_data = array();
		$find_fields = join('|', array_keys($this->content_fields));
		if (preg_match_all("#<div data-field=\"($find_fields)\">(.*?)</div><br><!-- end field -->#s", $post_text, $matches))
		{
			$fields_data = array_combine($matches[1], $matches[2]);
		}

		return array_intersect_key($fields_data, $this->content_fields);
	}

	/**
	 * @return void
	 */
	protected function reset()
	{
		$this->content_fields = array();
		$this->form_fields = array();
		$this->tpl_name = '';
		$this->view_mode = '';
	}
}
