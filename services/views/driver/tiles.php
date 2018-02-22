<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views\driver;

class tiles extends base_view
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\event\dispatcher_interface					$phpbb_dispatcher		Event dispatcher object
	 * @param \phpbb\language\language							$language				Language Object
	 * @param \phpbb\pagination									$pagination				Pagination object
	 * @param \phpbb\template\template							$template				Template object
	 * @param \blitze\content\services\fields					$fields					Content fields object
	 * @param \blitze\sitemaker\services\forum\data				$forum					Forum Data object
	 * @param \blitze\content\services\helper					$helper					Content helper object
	 * @param \blitze\content\services\quickmod					$quickmod				Quick moderator tools
	 * @param \blitze\content\services\topic\blocks_factory		$topic_blocks_factory	Topic blocks factory object
	 * @param \phpbb\request\request_interface					$request				Request object
	*/
	public function __construct(\phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\language\language $language, \phpbb\pagination $pagination, \phpbb\template\template $template, \blitze\content\services\fields $fields, \blitze\sitemaker\services\forum\data $forum, \blitze\content\services\helper $helper, \blitze\content\services\quickmod $quickmod, \blitze\content\services\topic\blocks_factory $topic_blocks_factory, \phpbb\request\request_interface $request)
	{
		parent::__construct($phpbb_dispatcher, $language, $pagination, $template, $fields, $forum, $helper, $quickmod, $topic_blocks_factory);

		$this->request = $request;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'tiles';
	}

	/**
	 * @inheritdoc
	 */
	public function get_langname()
	{
		return 'CONTENT_DISPLAY_TILES';
	}

	/**
	 * @inheritdoc
	 */
	public function get_index_template()
	{
		return 'views/tiles.html';
	}

	/**
	 * @inheritdoc
	 */
    public function render_index(\blitze\content\model\entity\type $entity, $page, array $filters, array $topic_data_overwrite = array())
	{
		parent::render_index($entity, $page, $filters);

		if ($this->request->is_ajax())
		{
			$this->template->assign_var('S_HIDE_HEADERS', true);
		}
	}
}
