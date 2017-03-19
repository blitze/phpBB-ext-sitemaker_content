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
	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\content\model\mapper_factory */
	protected $mapper_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth									$auth					Auth object
	 * @param \phpbb\language\language							$language				Language Object
	 * @param \phpbb\template\template							$template				Template object
	 * @param \phpbb\user										$user					User object
	 * @param \blitze\sitemaker\services\auto_lang				$auto_lang				Auto add lang file
	 * @param \blitze\content\services\types					$content_types			Content types object
	 * @param \blitze\content\services\form\fields_factory		$fields_factory			Fields factory  object
	 * @param \blitze\content\model\mapper_factory				$mapper_factory			Mapper factory object
	 * @param \blitze\content\services\views\views_factory		$views_factory			Views factory object
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\sitemaker\services\auto_lang $auto_lang, \blitze\content\services\types $content_types, \blitze\content\services\form\fields_factory $fields_factory, \blitze\content\model\mapper_factory $mapper_factory, \blitze\content\services\views\views_factory $views_factory)
	{
		parent::__construct($auth, $language, $template, $user, $auto_lang, $fields_factory, $views_factory);

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

		$content_desc_data = $entity->get_content_desc('edit');

		$this->template->assign_vars(array_merge(
			array_change_key_case($entity->to_array(), CASE_UPPER),
			array(
				'CONTENT_DESC'	=> $content_desc_data['text'],
			)
		));

		$this->generate_content_fields($entity->get_content_id());
	}

	/**
	 * @param int $content_id
	 * @return void
	 */
	protected function generate_content_fields($content_id)
	{
		$fields_mapper = $this->mapper_factory->create('fields');
		$collection = $fields_mapper->find(array(
			array('content_id', '=', $content_id),
		));

		foreach ($collection as $entity)
		{
			/** @var /blitze/content/services/form/field/field_interface $object */
			$object = $this->available_fields[$entity->get_field_type()];

			$explain = $entity->get_field_explain('edit');
			$this->template->assign_block_vars('field', array_merge(
				array_change_key_case($entity->to_array(), CASE_UPPER),
				array(
					'TOKEN'			=> '{' . strtoupper($entity->get_field_name()) . '}',
					'TYPE_LABEL'	=> $this->language->lang($object->get_langname()),
					'DEFAULT_TYPE'	=> $this->get_field_type($entity->get_field_type(), $object->get_default_props()),
					'FIELD_EXPLAIN'	=> $explain['text'],
				)
			));

			$this->display_field_options($entity->get_field_settings());
		}
	}

	/**
	 * @param array $field_settings
	 * @return void
	 */
	protected function display_field_options(array $field_settings)
	{
		if (isset($field_settings['field_options']))
		{
			$selected = array();
			if (isset($field_settings['field_defaults']))
			{
				$selected = array_flip($field_settings['field_defaults']);
			}

			foreach ($field_settings['field_options'] as $option)
			{
				$this->template->assign_block_vars('field.option', array(
					'VALUE'		=> $option,
					'S_CHECKED'	=> (isset($selected[$option])) ? true : false
				));
			}
		}
	}

	/**
	 * @param string $field_type
	 * @param array $props
	 * @return string
	 */
	protected function get_field_type($field_type, array $props)
	{
		return ($field_type === 'checkbox' || ($field_type === 'select' && $props['field_multi'])) ? 'checkbox' : 'radio';
	}
}
