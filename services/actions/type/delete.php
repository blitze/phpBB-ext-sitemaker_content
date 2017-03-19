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
use blitze\content\services\actions\action_utils;

class delete extends action_utils implements action_interface
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var\phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \blitze\content\services\types */
	protected $content_types;

	/** @var \blitze\sitemaker\services\forum\manager */
	protected $forum_manager;

	/** @var \blitze\content\model\mapper_factory */
	protected $content_mapper_factory;

	/** @var \blitze\sitemaker\model\mapper_factory */
	protected $sitemaker_mapper_factory;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\driver\driver_interface		$cache						Cache object
	 * @param \phpbb\language\language					$language					Language Object
	 * @param \phpbb\request\request_interface			$request					Request object
	 * @param \blitze\content\services\types			$content_types				Content types object
	 * @param \blitze\sitemaker\services\forum\manager	$forum_manager				Forum manager object
	 * @param \blitze\content\model\mapper_factory		$content_mapper_factory		Content Mapper factory object
	 * @param \blitze\sitemaker\model\mapper_factory	$sitemaker_mapper_factory	Sitemaker Mapper factory object
	 * @param bool										$auto_refresh				Used during testing
	 * @param bool										$trigger_error				Used during testing
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\language\language $language, \phpbb\request\request_interface $request, \blitze\content\services\types $content_types, \blitze\sitemaker\services\forum\manager $forum_manager, \blitze\content\model\mapper_factory $content_mapper_factory, \blitze\sitemaker\model\mapper_factory $sitemaker_mapper_factory, $auto_refresh = true, $trigger_error = true)
	{
		$this->cache = $cache;
		$this->language = $language;
		$this->request = $request;
		$this->content_types = $content_types;
		$this->forum_manager = $forum_manager;
		$this->content_mapper_factory = $content_mapper_factory;
		$this->sitemaker_mapper_factory = $sitemaker_mapper_factory;
		$this->auto_refresh = $auto_refresh;
		$this->trigger_error = $trigger_error;
	}

	/**
	 * @inheritdoc
	 */
	public function execute($u_action, $type = '')
	{
		if (!check_form_key('delete_content_type'))
		{
			$this->trigger_error($this->language->lang('FORM_INVALID'), $u_action);
		}

		$types_mapper = $this->content_mapper_factory->create('types');
		$entity = $this->content_types->get_type($type);

		$this->delete_content_type_forum($entity->get_forum_id());
		$this->delete_content_type_blocks($type);

		// Delete the content type
		$types_mapper->delete($entity);
		$this->cache->destroy('_content_types');

		$this->meta_refresh(3, $u_action);
		$this->trigger_error($this->language->lang('CONTENT_TYPE_DELETED'), $u_action, true);

	}

	/**
	 * @param int $forum_id
	 * @return void
	 */
	protected function delete_content_type_forum($forum_id)
	{
		$action_posts = $this->request->variable('action_posts', 'delete');
		$transfer_to_id = $this->request->variable('transfer_to_id', 0);

		$this->forum_manager->remove($forum_id, $action_posts, true, $transfer_to_id);
	}

	/**
	 * @param string $type
	 * @return void
	 */
	protected function delete_content_type_blocks($type)
	{
		$block_mapper = $this->sitemaker_mapper_factory->create('blocks');
		$collection = $block_mapper->find(array('name', 'LIKE', 'blitze.content.block%'));

		foreach ($collection as $entity)
		{
			$settings = $entity->get_settings();
			if ($settings['content_type'] === $type)
			{
				$block_mapper->delete($entity);
			}
		}
	}
}
