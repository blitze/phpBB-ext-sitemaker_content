<?php

/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\type;

use blitze\content\services\actions\action_utils;
use blitze\content\services\actions\action_interface;

class save extends action_utils implements action_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $logger;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\sitemaker\services\forum\manager */
	protected $forum_manager;

	/** @var \blitze\content\model\mapper_factory */
	protected $mapper_factory;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $php_ext;

	/** @var bool */
	protected $auto_refresh;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth							$auth					Auth object
	 * @param \phpbb\cache\driver\driver_interface		$cache					Cache object
	 * @param \phpbb\config\config						$config					Config object
	 * @param \phpbb\db\driver\driver_interface			$db						Database object
	 * @param \phpbb\event\dispatcher_interface			$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language					$language				Language Object
	 * @param \phpbb\log\log_interface					$logger					phpBB logger
	 * @param \phpbb\request\request_interface			$request				Template object
	 * @param \phpbb\user								$user					User object
	 * @param \blitze\content\services\types			$content_types			Content types object
	 * @param \blitze\sitemaker\services\forum\manager	$forum_manager			Forum manager object
	 * @param \blitze\content\model\mapper_factory		$mapper_factory			Mapper factory object
	 * @param string									$phpbb_admin_path       Relative admin root path
	 * @param string									$php_ext				php file extension
	 * @param boolean									$auto_refresh			Used for testing
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\driver\driver_interface $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\log\log_interface $logger, \phpbb\request\request_interface $request, \phpbb\user $user, \blitze\content\services\types $content_types, \blitze\sitemaker\services\forum\manager $forum_manager, \blitze\content\model\mapper_factory $mapper_factory, $phpbb_admin_path, $php_ext, $auto_refresh = true)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->language = $language;
		$this->logger = $logger;
		$this->request = $request;
		$this->user = $user;
		$this->content_types = $content_types;
		$this->forum_manager = $forum_manager;
		$this->mapper_factory = $mapper_factory;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $php_ext;
		$this->auto_refresh = $auto_refresh;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		$fields_data = $this->request->variable('field_data', array('' => array('' => '')), true);

		$types_mapper = $this->mapper_factory->create('types');
		$unsaved_entity = $this->get_unsaved_entity($types_mapper);

		$this->ensure_content_name_is_unique($unsaved_entity->get_content_name(), $type);
		$this->ensure_content_has_fields($fields_data);
		$this->db->sql_transaction('begin');

		$field_types = $this->get_field_types($fields_data);
		$old_langname = $this->handle_content_type($type, $unsaved_entity);

		/** @var \blitze\content\model\entity\type $entity */
		$entity = $types_mapper->save($unsaved_entity);

		/**
		 * Event to modify submitted field data before they are saved
		 *
		 * @event blitze.content.acp_save_fields_before
		 * @var	array									field_types			Array mapping field types to field names of form array([field_type] => array([field_name1], [field_name2]))
		 * @var	array									fields_data			Array containing field data of form array([field_name] => array([field_type] => 'foo', ...))
		 * @var	\blitze\content\model\entity\type		entity				Content type entity
		 */
		$vars = array('field_types', 'fields_data', 'entity');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.acp_save_fields_before', compact($vars)));

		$this->handle_content_fields($entity->get_content_id(), $fields_data);
		$this->db->sql_transaction('commit');
		$this->cache->destroy('_content_types');
		$this->show_results($entity, $u_action, $type, $old_langname);
	}

	/**
	 * @param string $type
	 * @param \blitze\content\model\entity\type $unsaved_entity
	 * @return string
	 */
	protected function handle_content_type($type, \blitze\content\model\entity\type &$unsaved_entity)
	{
		$existing_langname = '';
		$forum_perm_from = $this->request->variable('copy_forum_perm', 0);

		if ($type)
		{
			$entity = $this->content_types->get_type($type);
			$forum_id = $entity->get_forum_id();
			$existing_langname = $entity->get_content_langname();

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

		return $existing_langname;
	}

	/**
	 * @param \blitze\content\model\mapper\types $mapper
	 * @return \blitze\content\model\entity\type
	 */
	protected function get_unsaved_entity(\blitze\content\model\mapper\types $mapper)
	{
		$content_desc = $this->request->variable('content_desc', '', true);
		$content_view = $this->request->variable('content_view', '');
		$comments = $this->request->variable('comments', '');
		$view_settings = $this->request->variable(array('view_settings', $content_view), array('' => ''));
		$comments_settings = $this->request->variable(array('comments_settings', $comments), array('' => ''));

		$entity = $mapper->create_entity(array(
			'content_name'			=> $this->request->variable('content_name', ''),
			'content_langname'		=> $this->request->variable('content_langname', '', true),
			'content_enabled'		=> $this->request->variable('content_enabled', true),
			'content_view'			=> $content_view,
			'content_view_settings'	=> $view_settings,
			'comments'				=> $comments,
			'comments_settings'		=> $comments_settings,
			'req_approval'			=> $this->request->variable('req_approval', 1),
			'allow_views'			=> $this->request->variable('allow_views', 0),
			'show_pagination'		=> $this->request->variable('show_pagination', 0),
			'index_show_desc'		=> $this->request->variable('index_show_desc', 0),
			'items_per_page'		=> $this->request->variable('items_per_page', 1),
			'summary_tpl'			=> $this->request->variable('summary_tpl', '', true),
			'detail_tpl'			=> $this->request->variable('detail_tpl', '', true),
			'topic_blocks'			=> $this->get_topic_blocks(),
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
	 * @param \blitze\content\model\entity\type $entity
	 * @param string $u_action
	 * @param string $type
	 * @param string $old_langname
	 * @return void
	 */
	protected function show_results(\blitze\content\model\entity\type $entity, $u_action, $type, $old_langname)
	{
		if (!$type)
		{
			$u_set_permission = append_sid("{$this->phpbb_admin_path}index.$this->php_ext", 'i=permissions&mode=setting_forum_local&forum_id[]=' . $entity->get_forum_id(), true);
			$lang_key = 'CONTENT_TYPE_CREATED';
			$message = $this->language->lang($lang_key, '<a href="' . $u_set_permission . '">', '</a>');
		}
		else
		{
			$this->meta_refresh(3, $u_action);
			$lang_key = 'CONTENT_TYPE_UPDATED';
			$message = $this->language->lang($lang_key);
		}

		$additional_data = array();
		if ($type && $entity->get_content_name() !== $type)
		{
			$lang_key = 'CONTENT_TYPE_RENAMED';
			$additional_data[] = $old_langname;
		}

		$additional_data[] = $entity->get_content_langname();
		$this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_LOG_' . $lang_key, time(), $additional_data);

		$this->trigger_error($message, $u_action);
	}

	/**
	 * @param string $test_name
	 * @param string $content_type
	 * @return void
	 * @throws \blitze\sitemaker\exception\invalid_argument
	 */
	protected function ensure_content_name_is_unique($test_name, $content_type)
	{
		if ($test_name !== $content_type && $this->content_types->exists($test_name))
		{
			throw new \blitze\sitemaker\exception\invalid_argument(array($test_name, 'CONTENT_NAME_EXISTS'));
		}
	}

	/**
	 * @param array $fields_data
	 * @return void
	 * @throws \blitze\sitemaker\exception\invalid_argument
	 */
	protected function ensure_content_has_fields(array $fields_data)
	{
		if (!sizeof(array_filter($fields_data)))
		{
			throw new \blitze\sitemaker\exception\invalid_argument(array('content_fields', 'FIELD_MISSING'));
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

		$this->forum_manager->add($forum_data, $forum_perm_from);

		return (int) $forum_data['forum_id'];
	}

	/**
	 * @param int $content_id
	 * @param array $fields_data
	 * @return void
	 */
	protected function handle_content_fields($content_id, array $fields_data)
	{
		$mapper = $this->mapper_factory->create('fields');

		$fields_ary = array_filter(array_keys($fields_data));
		$field_ids = $this->get_existing_field_ids($content_id);
		$max_id = $mapper->get_max_field_id();

		$form_fields = array();
		foreach ($fields_ary as $i => $field)
		{
			/** @var \blitze\content\model\entity\field $entity */
			$entity = $mapper->create_entity($fields_data[$field]);
			$entity->set_field_id(isset($field_ids[$field]) ? $field_ids[$field] : ++$max_id)
				->set_content_id($content_id)
				->set_field_order($i)
				->set_field_explain($fields_data[$field]['field_explain'], 'storage')
				->set_field_props($this->get_field_props($field));

			$form_fields[$field] = $entity->to_db();
		}

		// delete all fields for this content type
		$mapper->delete(array('content_id', '=', $content_id));

		// add the submitted fields
		$mapper->multi_insert($form_fields);
	}

	/**
	 * @param string $field
	 * @return array
	 */
	protected function get_field_props($field)
	{
		$field_props = $this->request->variable(array('field_props', $field), array('' => ''));
		$field_options = $this->request->variable(array('field_options', $field), array(0 => ''), true);
		$fields_defaults = $this->request->variable(array('field_defaults', $field), array(0 => ''), true);

		$field_props = array_filter($field_props, 'strlen');
		$field_props = array_merge($field_props, array_filter(array(
			'options'	=> array_filter($field_options, 'strlen'),
			'defaults'	=> array_filter($fields_defaults, 'strlen'),
		)));

		return $field_props;
	}

	/**
	 * @param int $content_id
	 * @return array
	 */
	protected function get_existing_field_ids($content_id)
	{
		$mapper = $this->mapper_factory->create('fields');
		$collection = $mapper->find(array(
			array('content_id', '=', $content_id),
		));

		$field_ids = array();
		foreach ($collection as $id => $entity)
		{
			$field_ids[$entity->get_field_name()] = $id;
		}

		return $field_ids;
	}

	/**
	 * @param array $fields_data
	 * @return array[]
	 */
	protected function get_field_types(array $fields_data)
	{
		$field_types = array();
		foreach ($fields_data as $field => $row)
		{
			$field_types[$row['field_type']][] = $field;
		}

		return $field_types;
	}

	/**
	 * @return array
	 */
	protected function get_topic_blocks()
	{
		$topic_blocks = $this->request->variable('topic_blocks', '');

		$services = explode(',', $topic_blocks);
		$settings = array_fill_keys($services, []);

		foreach ($services as $service)
		{
			$settings[$service] = $this->request->variable(array('topic_block_settings', $service), array('' => ''));
		}

		return $settings;
	}
}
