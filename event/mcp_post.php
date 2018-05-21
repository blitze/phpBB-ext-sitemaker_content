<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2018 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class mcp_post implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/* @var \blitze\content\services\fields */
	protected $fields;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \blitze\content\services\types		$content_types		Content types object
	 * @param \blitze\content\services\fields		$fields				Content fields object
	 * @param string								$phpbb_root_path	Path to the phpbb includes directory.
	 * @param string								$php_ext			php file extension
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \blitze\content\services\types $content_types, \blitze\content\services\fields $fields, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->content_types = $content_types;
		$this->fields = $fields;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.mcp_post_template_data'	=> 'modify_post_data',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function modify_post_data(\phpbb\event\data $event)
	{
		$type = (string) $this->content_types->get_forum_type($event['post_info']['forum_id']);
		if ($type && $event['post_info']['post_id'] === $event['post_info']['topic_first_post_id'])
		{
			$entity = $this->content_types->get_type($type);

			$this->fields->prepare_to_show($entity, array($event['post_info']['topic_id']), $entity->get_summary_fields(), $entity->get_summary_tpl(), 'summary');
			$users_cache = $attachments = $topic_tracking_info = $update_count = array();

			$post_data = $topic_data = (array) $event['post_info'];
			$users_cache[$post_data['poster_id']] = array();

			$tpl_data = $this->fields->get_summary_template_data($type, $topic_data, $post_data, $users_cache, $attachments, $topic_tracking_info, $update_count);
			$content = $this->fields->build_content($tpl_data);

			$mcp_post_template_data = $event['mcp_post_template_data'];
			$mcp_post_template_data['POST_PREVIEW'] = isset($content['CUSTOM_DISPLAY']) ? $content['CUSTOM_DISPLAY'] : join('', $content['FIELDS']['all']);
			$event['mcp_post_template_data'] = $mcp_post_template_data;
		}
	}
}
