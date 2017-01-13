<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\type;

class save
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\db */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\sitemaker\services\forum\manager */
	protected $forum;

	/** @var \blitze\content\model\mapper_factory */
	protected $mapper_factory;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth					Auth object
	 * @param \phpbb\cache\service						$cache					Cache object
	 * @param \phpbb\config\db							$config					Config object
	 * @param \phpbb\db\driver\driver_interface			$db						Database object
	 * @param \phpbb\language\language					$language				Language Object
	 * @param \phpbb\request\request_interface			$request				Request object
	 * @param \phpbb\template\template					$template				Template object
	 * @param \blitze\content\services\types			$content_types			Content types object
	 * @param \blitze\sitemaker\services\forum\manager	$forum					Forum manager object
	 * @param \blitze\content\model\mapper_factory		$mapper_factory			Mapper factory object
	 * @param string									$phpbb_admin_path       Relative admin root path
	 * @param string									$php_ext				php file extension
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\db $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request_interface $request, \phpbb\template\template $template, \blitze\content\services\types $content_types, \blitze\sitemaker\services\forum\manager $forum, \blitze\content\model\mapper_factory $mapper_factory, $phpbb_admin_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->content_types = $content_types;
		$this->forum = $forum;
		$this->mapper_factory = $mapper_factory;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		$fields_data = $this->request->variable('fdata', array('' => array('' => '')));

		$types_mapper = $this->mapper_factory->create('types');
		$unsaved_entity = $this->get_unsaved_entity($types_mapper);

		$this->ensure_content_name_is_unique($unsaved_entity->get_content_name(), $type);
		$this->ensure_content_has_fields($fields_data);
		$this->db->sql_transaction('begin');

		$this->handle_content_type($type, $unsaved_entity);
		/** @var \blitze\content\model\entity\type $entity */
		$entity = $types_mapper->save($unsaved_entity);

		$this->handle_content_fields($entity->get_content_id(), $fields_data);
		$this->db->sql_transaction('commit');
		$this->cache->destroy('_content_types');
		$this->show_results($entity->get_forum_id(), $u_action, $type);
	}

	/**
	 * @param string $type
	 * @param \blitze\content\model\entity\type $unsaved_entity
	 * @return void
	 */
	protected function handle_content_type($type, \blitze\content\model\entity\type &$unsaved_entity)
	{
		$forum_perm_from = $this->request->variable('copy_forum_perm', 0);

		if ($type)
		{
			$entity = $this->content_types->get_type($type);
			$forum_id = $entity->get_forum_id();

			$unsaved_entity->set_forum_id($forum_id);
			$unsaved_entity->set_content_id($entity->get_content_id());
			$this->handle_langname_change($forum_id, $entity->get_content_langname(), $unsaved_entity->get_content_langname());
			$this->copy_forum_permissions($forum_id, $forum_perm_from);
		}
		else
		{
			$forum_id = $this->create_content_forum($unsaved_entity->get_content_langname(), $forum_perm_from);
			$unsaved_entity->set_forum_id($forum_id);
		}
	}

	/**
	 * @param \blitze\content\model\mapper\types $mapper
	 * @return \blitze\content\model\entity\type
	 */
	protected function get_unsaved_entity(\blitze\content\model\mapper\types $mapper)
	{
		$content_desc = $this->request->variable('content_desc', '', true);
		$content_view = $this->request->variable('content_view', '');
		$view_settings = $this->request->variable('view_settings', array('' => array('' => '')));

		$entity = $mapper->create_entity(array(
			'content_name'			=> $this->request->variable('content_name', ''),
			'content_langname'		=> $this->request->variable('content_langname', '', true),
			'content_enabled'		=> $this->request->variable('content_enabled', true),
			'content_view'			=> $content_view,
			'content_view_settings'	=> isset($view_settings[$content_view]) ? $view_settings[$content_view] : '',
			'req_approval'			=> $this->request->variable('req_approval', 1),
			'allow_comments'		=> $this->request->variable('allow_comments', 0),
			'allow_views'			=> $this->request->variable('allow_views', 0),
			'show_poster_info'		=> $this->request->variable('show_info', 0),
			'show_poster_contents'	=> $this->request->variable('show_contents', 0),
			'show_pagination'		=> $this->request->variable('show_pagination', 0),
			'index_show_desc'		=> $this->request->variable('index_show_desc', 0),
			'items_per_page'		=> $this->request->variable('items_per_page', 1),
			'summary_tpl'			=> $this->request->variable('summary_tpl', '', true),
			'detail_tpl'			=> $this->request->variable('detail_tpl', '', true),
			'last_modified'			=> time(),
		));
		return $entity->set_content_desc($content_desc, 'storage');
	}

	/**
	 * @param int $forum_id
	 * @param int $forum_perm_from
	 * @return void
	 */
	protected function copy_forum_permissions($forum_id, $forum_perm_from)
	{
		if ($forum_perm_from && $forum_perm_from != $forum_id)
		{
			copy_forum_permissions($forum_perm_from, array($forum_id), false, false);
			phpbb_cache_moderators($this->db, $this->cache, $this->auth);

			$this->auth->acl_clear_prefetch();
			$this->cache->destroy('sql', FORUMS_TABLE);
		}
	}

	/**
	 * @param int $forum_id
	 * @param string $u_action
	 * @param string $type
	 * @return void
	 */
	protected function show_results($forum_id, $u_action, $type)
	{
		if (!$type)
		{
			$u_set_permission = append_sid("{$this->phpbb_admin_path}index.$this->php_ext", 'i=permissions&mode=setting_forum_local&forum_id[]=' . $forum_id, true);
			$message = $this->language->lang('CONTENT_TYPE_CREATED', '<a href="' . $u_set_permission . '">', '</a>');
		}
		else
		{
			meta_refresh(3, $u_action);
			$message = $this->language->lang('CONTENT_TYPE_UPDATED');
		}

		trigger_error($message . adm_back_link($u_action));
	}

	/**
	 * @param string $test_name
	 * @param string $content_type
	 * @return void
	 */
	protected function ensure_content_name_is_unique($test_name, $content_type)
	{
		if ($test_name !== $content_type && $this->content_types->exists($test_name))
		{
			trigger_error($this->language->lang('CONTENT_NAME_EXISTS', $test_name), E_USER_WARNING);
		}
	}

	/**
	 * @param array $fields_data
	 * @return void
	 */
	protected function ensure_content_has_fields(array $fields_data)
	{
		if (!sizeof($fields_data))
		{
			trigger_error($this->language->lang('MISSING_CONTENT_FIELDS'), E_USER_WARNING);
		}
	}

	/**
	 * @param int $forum_id
	 * @param string $old_langname
	 * @param string $new_langname
	 * @return void
	 */
	protected function handle_langname_change($forum_id, $old_langname, $new_langname)
	{
		if ($old_langname !== $new_langname)
		{
			$forum_name = $this->language->lang($new_langname);
			$sql = 'UPDATE ' . FORUMS_TABLE . " SET forum_name = '" . $this->db->sql_escape($forum_name) . "' WHERE forum_id = " . (int) $forum_id;
			$this->db->sql_query($sql);
		}
	}

	/**
	 * @param string $content_langname
	 * @param int $forum_perm_from
	 * @return int
	 */
	protected function create_content_forum($content_langname, $forum_perm_from)
	{
		$forum_data = array(
			'forum_type'	=> FORUM_POST,
			'forum_name'	=> $this->language->lang($content_langname),
			'forum_desc'	=> $this->language->lang('CONTENT_FORUM_EXPLAIN'),
			'parent_id'		=> (int) $this->config['blitze_content_forum_id'],
		);
		$errors = $this->forum->add($forum_data, $forum_perm_from);

		if (sizeof($errors))
		{
			trigger_error($this->language->lang('CONTENT_CREATE_FORUM_ERROR', implode('<br />', $errors)), E_USER_WARNING);
		}

		return (int) $forum_data['forum_id'];
	}

	/**
	 * @param int $content_id
	 * @param array $fields_data
	 * @return void
	 */
	protected function handle_content_fields($content_id, array $fields_data)
	{
		$fields_settings = $this->request->variable('fsettings', array('' => array('' => '')), true);
		$fields_defaults = $this->request->variable('fdefaults', array('' => array('' => '')), true);

		$mapper = $this->mapper_factory->create('fields');

		// delete all fields for this content type
		$mapper->delete(array('content_id', '=', $content_id));

		$form_fields = array();
		$max_id = $mapper->get_max_field_id();
		$fields_ary = array_filter(array_keys($fields_data));

		foreach ($fields_ary as $i => $field)
		{
			/** @var \blitze\content\model\entity\field $entity */
			$entity = $mapper->create_entity($fields_data[$field]);
			$entity->set_field_id($max_id + $i + 1)
				->set_content_id($content_id)
				->set_field_order($i)
				->set_field_explain($fields_data[$field]['field_explain'], 'storage')
				->set_field_settings($this->get_field_settings($field, $fields_settings, $fields_defaults));

			$form_fields[$field] = $entity->to_db();
		}

		$mapper->multi_insert($form_fields);
	}

	/**
	 * @param string $field
	 * @param array $fields_settings
	 * @param array $fields_defaults
	 * @return array
	 */
	protected function get_field_settings($field, array $fields_settings, array $fields_defaults)
	{
		$field_options = $this->request->variable($field . '_options', array(''), true);

		if (sizeof($field_options))
		{
			$fields_settings[$field]['field_options'] = array_combine($field_options, $field_options);
		}

		if (isset($fields_defaults[$field]))
		{
			$fields_settings[$field]['field_defaults'] = $fields_defaults[$field];
		}

		return isset($fields_settings[$field]) ? $fields_settings[$field] : '';
	}
}
