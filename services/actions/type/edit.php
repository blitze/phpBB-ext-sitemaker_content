<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\type;

class edit extends add
{
	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\content\model\mapper_factory */
	protected $mapper_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth									$auth					Auth object
	 * @param \phpbb\controller\helper                          $controller_helper      Controller Helper object
	 * @param \phpbb\language\language							$language				Language Object
	 * @param \phpbb\template\template							$template				Template object
	 * @param \phpbb\user										$user					User object
	 * @param \blitze\sitemaker\services\auto_lang				$auto_lang				Auto add lang file
	 * @param \blitze\content\services\form\fields_factory		$fields_factory			Fields factory  object
	 * @param \blitze\content\services\views\views_factory		$views_factory			Views factory object
	 * @param \phpbb\event\dispatcher_interface					$phpbb_dispatcher		Event dispatcher object
	 * @param \blitze\content\services\types					$content_types			Content types object
	 * @param \blitze\content\model\mapper_factory				$mapper_factory			Mapper factory object
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $controller_helper, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\sitemaker\services\auto_lang $auto_lang, \blitze\content\services\form\fields_factory $fields_factory, \blitze\content\services\views\views_factory $views_factory, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \blitze\content\services\types $content_types, \blitze\content\model\mapper_factory $mapper_factory)
	{
		parent::__construct($auth, $controller_helper, $language, $template, $user, $auto_lang, $fields_factory, $views_factory);

		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->content_types = $content_types;
		$this->mapper_factory = $mapper_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		$entity = $this->content_types->get_type($type);

		parent::execute($u_action, $type, $entity->get_content_view(), $entity->get_forum_id());

		$this->template->assign_vars(array_change_key_case(array_merge($entity->to_array(), array(
			'content_desc'		=> $entity->get_content_desc('edit'),
			'content_fields'	=> $this->generate_content_fields($entity->get_content_id()),
			'from_db'			=> true,
		)), CASE_UPPER));
	}

	/**
	 * @param int $content_id
	 * @return array
	 */
	protected function generate_content_fields($content_id)
	{
		$fields_mapper = $this->mapper_factory->create('fields');
		$collection = $fields_mapper->find(array(
			array('content_id', '=', $content_id),
		));

		$content_fields = array();
		foreach ($collection as $entity)
		{
			/** @var \blitze\content\services\form\field\field_interface $field_instance */
			$field_instance = $this->available_fields[$entity->get_field_type()];
			$field_data = $entity->to_array();
			$field_data['field_props'] = array_replace_recursive($field_instance->get_default_props(), $field_data['field_props']);

			/**
			 * Event to modify field data
			 *
			 * @event blitze.content.acp_modify_field_data
			 * @var	int														content_id		Content type id
			 * @var	array													field_data		Array containing field data
			 * @var	\blitze\content\services\form\field\field_interface		field_instance	Field instance
			 */
			$vars = array('content_id', 'field_data', 'field_instance');
			extract($this->phpbb_dispatcher->trigger_event('blitze.content.acp_modify_field_data', compact($vars)));

			$content_fields[] = array_change_key_case(array_merge($field_data, array(
				'type_label'	=> $field_instance->get_langname(),
				'field_explain'	=> $entity->get_field_explain('edit'),
			)), CASE_UPPER);
		}

		return $content_fields;
	}
}
