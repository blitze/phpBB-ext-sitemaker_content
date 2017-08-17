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
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

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
	 * @param \phpbb\auth\auth									$auth					Auth object
	 * @param \phpbb\controller\helper							$controller_helper		Controller Helper object
	 * @param \phpbb\event\dispatcher_interface					$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language							$language				Language Object
	 * @param \phpbb\template\template							$template				Template object
	 * @param \phpbb\user										$user					User object
	 * @param \blitze\sitemaker\services\auto_lang				$auto_lang				Auto add lang file
	 * @param \blitze\content\services\form\fields_factory		$fields_factory			Fields factory  object
	 * @param \blitze\content\services\views\views_factory		$views_factory			Views factory object
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $controller_helper, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\user $user, \blitze\sitemaker\services\auto_lang $auto_lang, \blitze\content\services\form\fields_factory $fields_factory, \blitze\content\services\views\views_factory $views_factory)
	{
		$this->auth = $auth;
		$this->controller_helper = $controller_helper;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
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

		$tpl_data = array(
			'VIEW'				=> $view,
			'CONTENT_VIEWS'		=> $this->views_factory->get_all_views(),
			'POST_AUTHOR'		=> $this->user->data['username'],
			'POST_DATE'			=> $this->user->format_date(time()),
			'ITEMS_PER_PAGE'	=> 10,
			'TOPICS_PER_GROUP'	=> 4,

			'U_ACTION'			=> $u_action . "&amp;do=save&amp;type=$type",
			'UA_AJAX_URL'		=> $this->controller_helper->route('blitze_content_field_settings', array(), false),

			'S_TYPE_OPS'				=> $this->get_field_options(),
			'S_FORUM_OPTIONS'			=> make_forum_select(false, $forum_id, true, false, false),
			'S_CAN_COPY_PERMISSIONS'	=> ($this->auth->acl_get('a_fauth') && $this->auth->acl_get('a_authusers') && $this->auth->acl_get('a_authgroups') && $this->auth->acl_get('a_mauth')) ? true : false,
			'S_EDIT'					=> true,
		);

		/**
		 * Event to modify template data
		 *
		 * @event blitze.content.acp_modify_edit_template_data
		 * @var	array	tpl_data	Array containing template data
		 */
		$vars = array('tpl_data');
		extract($this->phpbb_dispatcher->trigger_event('blitze.content.acp_modify_edit_template_data', compact($vars)));

		$this->template->assign_vars($tpl_data);
	}

	/**
	 * @pram string
	 */
	protected function get_field_options()
	{
		$fields = $this->available_fields;
		unset($fields['hidden']);

		$options = '';
		foreach ($fields as $field => $object)
		{
			$options .= '<option value="' . $field . '">' . $this->language->lang($object->get_langname()) . '</option>';
		}

		return $options;
	}
}
