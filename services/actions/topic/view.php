<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\actions\topic;

use blitze\content\services\actions\action_interface;

class view implements action_interface
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\content\services\views\views_factory */
	protected $views;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface				$request				Request object
	 * @param \phpbb\template\template						$template				Template object
	 * @param \blitze\content\services\types				$content_types			Content types object
	 * @param \blitze\content\services\views\views_factory	$views					Views factory object
	 * @param string										$phpbb_root_path		Path to the phpbb includes directory.
	 * @param string										$php_ext				php file extension
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\template\template $template, \blitze\content\services\types $content_types, \blitze\content\services\views\views_factory $views, $phpbb_root_path, $php_ext)
	{
		$this->request = $request;
		$this->template = $template;
		$this->content_types = $content_types;
		$this->views = $views;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $mode = '')
	{
		$topic_id = $this->request->variable('t', 0);
		$type = $this->request->variable('type', '');
		$redirect_url = $this->request->variable('redirect', $u_action);

		$view_tpl = '';
		if (($entity = $this->content_types->get_type($type)) !== false)
		{
			$entity->set_topic_blocks('');
			$entity->set_allow_comments(false);

			$update_count = array();
			$overwrite = $this->get_data_overwrite($mode, $u_action, $type, $redirect_url, $topic_id);

			/** @var \blitze\content\services\views\driver\views_interface $view_handler */
			$view_handler = $this->views->get($entity->get_content_view());
			$view_handler->render_detail($entity, $topic_id, 'detail', $redirect_url, $update_count, $overwrite);
			$view_tpl = $view_handler->get_detail_template();
		}

		$this->template->assign_vars(array(
			'MODE'				=> $mode,
			'S_HIDE_HEADERS'	=> true,
			'S_VIEWING'			=> $view_tpl,
		));
	}

	/**
	 * Overwrite template data
	 *
	 * @param string $mode
	 * @param string $u_action
	 * @param string $type
	 * @param string $redirect_url
	 * @param int $topic_id
	 * @return string[]
	 */
	protected function get_data_overwrite($mode, $u_action, $type, $redirect_url, $topic_id)
	{
		$overwrite = array(
			'TOPIC_URL'	=> $u_action . "&amp;do=view&amp;type=$type&amp;t=$topic_id&amp;redirect=$redirect_url",
			'U_INFO'	=> '',
		);

		if ($mode === 'mcp')
		{
			$overwrite['U_DELETE'] = append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'quickmod=1&amp;action=delete_topic&amp;t=' . $topic_id . '&amp;redirect=' . $redirect_url);
		}

		return $overwrite;
	}
}
