<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\type;

use blitze\content\services\actions\action_interface;

class add implements action_interface
{
	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \blitze\sitemaker\services\auto_lang */
	protected $auto_lang;

	/** @var \blitze\content\services\form\fields_factory */
	protected $fields_factory;

	/** @var \blitze\content\services\views\views_factory */
	protected $views_factory;

	/** @var array */
	protected $available_fields = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language							$language				Language Object
	 * @param \phpbb\template\template							$template				Template object
	 * @param \phpbb\user										$user					User object
	 * @param \blitze\sitemaker\services\auto_lang				$auto_lang				Auto add lang file
	 * @param \blitze\content\services\form\fields_factory		$fields_factory			Fields factory  object
	 * @param \blitze\content\services\views\views_factory		$views_factory			Views factory object
	*/
	public function __construct(\phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\sitemaker\services\auto_lang $auto_lang, \blitze\content\services\form\fields_factory $fields_factory, \blitze\content\services\views\views_factory $views_factory)
	{
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->auto_lang = $auto_lang;
		$this->fields_factory = $fields_factory;
		$this->views_factory = $views_factory;
	}

	/**
	 * @param string $u_action
	 * @param string $type
	 * @param string $view
	 * @param int $forum_id
	 * @return void
	 */
	public function execute($u_action, $type = '', $view = 'blitze.content.view.portal', $forum_id = 0)
	{
		$this->auto_lang->add('form_fields');
		$this->available_fields = $this->fields_factory->get_all();
		$this->set_view_options($view);

		$this->template->assign_vars(array(
			'POST_AUTHOR'		=> $this->user->data['username'],
			'POST_DATE'			=> $this->user->format_date(time()),
			'ITEMS_PER_PAGE'	=> 10,
			'TOPICS_PER_GROUP'	=> 4,
			'U_ACTION'			=> $u_action . "&amp;do=save&amp;type=$type",

			'S_TYPE_OPS'				=> $this->get_field_options(),
			'S_FORUM_OPTIONS'			=> make_forum_select(false, $forum_id, true, false, false),
			'S_CAN_COPY_PERMISSIONS'	=> true,
			'S_EDIT'					=> true,
		));
	}

	/**
	 * @param string $view
	 * @return void
	 */
	protected function set_view_options($view)
	{
		$views = $this->views_factory->get_all_views();

		foreach ($views as $service => $label)
		{
			$this->template->assign_block_vars('view', array(
				'LABEL'			=> $this->language->lang($label),
				'VALUE'			=> $service,
				'S_SELECTED'	=> ($service === $view) ? true : false,
			));
		}
	}

	/**
	 * @pram string
	 */
	protected function get_field_options()
	{
		$fields = $this->available_fields;
		unset($fields['reset'], $fields['submit']);

		$options = '';
		foreach ($fields as $object)
		{
			$options .= '<option value="' . $object->get_name() . '">' . $this->language->lang($object->get_langname()) . "</option>\n";
		}

		return $options;
	}
}
