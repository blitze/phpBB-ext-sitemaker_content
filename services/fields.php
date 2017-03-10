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
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\form\fields_factory */
	protected $fields_factory;

	/** @var array */
	protected $form_fields;

	/** @var array */
	protected $content_fields;

	/** @var array */
	protected $tags = array();

	/** @var string */
	protected $tpl_name = '';

	/** @var string */
	protected $view_mode = '';

	/** @var array */
	protected $label = array('label-hidden', 'label-inline', 'label-newline');

	/**
	 * Construct
	 *
	 * @param \phpbb\config\db								$config					Config object
	 * @param \phpbb\content_visibility						$content_visibility		Phpbb Content visibility object
	 * @param \phpbb\controller\helper						$controller_helper		Controller Helper object
	 * @param \phpbb\event\dispatcher_interface				$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language						$language				Language object
	 * @param \phpbb\template\template						$template				Template object
	 * @param \phpbb\user									$user					User object
	 * @param \blitze\content\services\form\fields_factory	$fields_factory			Form fields factory
	 * @param \blitze\content\services\helper				$helper					Content helper object
	 */
	public function __construct(\phpbb\config\db $config, \phpbb\content_visibility $content_visibility, \phpbb\controller\helper $controller_helper, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\content\services\form\fields_factory $fields_factory, \blitze\content\services\helper $helper)
	{
		parent::__construct($config, $content_visibility, $controller_helper, $phpbb_dispatcher, $language, $user, $helper);

		$this->template = $template;
		$this->fields_factory = $fields_factory;
	}

	/**
	 * Set type data needed to display posts
	 *
	 * @param string $content_type
	 * @param string $view_mode
	 * @param array $view_mode_fields
	 * @param array $fields_data
	 * @param string $custom_tpl
	 * @param string $tpl_name
	 * @param int $force_max_chars
	 * @return void
	 */
	public function prepare_to_show($content_type, $view_mode, array $view_mode_fields, array $fields_data, $custom_tpl = '', $tpl_name = '', $force_max_chars = 0)
	{
		$this->reset();

		if (!empty($custom_tpl))
		{
			$this->tpl_name	= ($tpl_name) ? $tpl_name : $content_type . '_' . $view_mode;
		}

		$this->view_mode = (in_array($view_mode, array('summary', 'detail'))) ? $view_mode : 'summary';
		$this->form_fields = array_intersect_key($this->fields_factory->get_all(), array_flip($view_mode_fields));
		$this->set_content_fields($view_mode_fields, $fields_data, $force_max_chars);
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
	public function show($type, array $topic_data, array $post_data, array $users_cache, array $attachments, array &$update_count, array $topic_tracking_info, array $topic_data_overwrite = array(), $mode = '')
	{
		$callable = 'get_' . $this->view_mode . '_template_data';
		$tpl_data = array_merge(
			$this->{$callable}($type, $topic_data, $post_data, $users_cache, $attachments, $update_count, $topic_tracking_info, $mode),
			$topic_data_overwrite
		);

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
			$this->template->assign_vars(array_change_key_case(array_merge($tpl_data, $fields_data, array(
				'S_USER_LOGGED_IN' => true
			)), CASE_UPPER));
			$this->template->set_filenames(array('content' => $this->tpl_name));
			$tpl_data['CUSTOM_DISPLAY'] = $this->template->assign_display('content');
		}
		else
		{
			$tpl_data['SEQ_DISPLAY'] = join('<br />', $fields_data);
		}

		return $tpl_data;
	}

	/**
	 * @param array $view_mode_fields
	 * @param array $fields_data
	 * @param int $force_max_chars
	 * @return void
	 */
	protected function set_content_fields(array $view_mode_fields, array $fields_data, $force_max_chars)
	{
		foreach ($view_mode_fields as $name => $field_type)
		{
			if (isset($this->form_fields[$field_type]))
			{
				if ($force_max_chars && $field_type === 'textarea')
				{
					$fields_data[$name]['field_settings']['max_chars'] = $force_max_chars;
				}

				$this->tags[$name] = $name;
				$this->content_fields[$name] = $fields_data[$name];
			}
		}
	}

	/**
	 * @param array $tpl_data
	 * @return array
	 */
	protected function get_fields_data_for_display(array &$tpl_data)
	{
		$fields_data = array();
		$post_field_data = $this->get_fields_data_from_post($tpl_data['MESSAGE']);
		unset($tpl_data['MESSAGE']);

		foreach ($this->content_fields as $field_name => $row)
		{
			$field_type		= $row['field_type'];
			$field_value	= &$post_field_data[$field_name];
			$field_contents	= $this->form_fields[$field_type]->display_field($field_value, $this->view_mode, $tpl_data, $row);

			// this essentially hides other fields if the field returns an array
			if (is_array($field_contents))
			{
				$fields_data = $field_contents;
				break;
			}

			$fields_data[$field_name] = '<div class="field-label ' . $this->label[$row['field_' . $this->view_mode . '_ldisp']] . '">' . $row['field_label'] . ': </div>' . $field_contents;
		}

		return $fields_data;
	}

	/**
	 * @param string $post_text
	 * @return array
	 */
	protected function get_fields_data_from_post($post_text)
	{
		$fields_data = array();
		$find_tags = join('|', $this->tags);
		if (preg_match_all("#<div data-field=\"($find_tags)\">(.*?)</div><br><!-- end field -->#s", $post_text, $matches))
		{
			$fields_data = array_combine($matches[1], $matches[2]);
		}

		return array_intersect_key($fields_data, $this->tags);
	}

	/**
	 * @return void
	 */
	protected function reset()
	{
		$this->tags = array();
		$this->content_fields = array();
		$this->form_fields = array();
		$this->tpl_name = '';
		$this->view_mode = '';
	}
}
