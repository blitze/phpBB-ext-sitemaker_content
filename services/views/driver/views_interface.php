<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2016 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\views\driver;

interface views_interface
{
	/**
	 * Short name of content view
	 * @return string
	 */
	public function get_name();

	/**
	 * Lang name of content view
	 * @return string
	 */
	public function get_langname();

	/**
	 * Template file of content index
	 * @return string
	 */
	public function get_index_template();

	/**
	 * Template file of content details
	 * @return string
	 */
	public function get_detail_template();

	/**
	 * @param array $filters
	 * @param \blitze\content\model\entity\type $entity
	 * @return void
	 */
	public function build_index_query(array $filters, \blitze\content\model\entity\type $entity = null);

	/**
	 * Display topics on content index
	 *
	 * @param \blitze\content\model\entity\type $entity
	 * @param int $page
	 * @param array $filters
	 * @param array $topic_data_overwrite
	 * @return int
	 */
	public function render_index(\blitze\content\model\entity\type $entity, $page, array $filters, array $topic_data_overwrite = array());

	/**
	 * Show topic details
	 *
	 * @param \blitze\content\model\entity\type $entity
	 * @param int $topic_id
	 * @param array $update_count
	 * @param array $topic_data_overwrite
	 * @return array
	 */
	public function render_detail(\blitze\content\model\entity\type $entity, $topic_id, array &$update_count, array $topic_data_overwrite = array());
}
