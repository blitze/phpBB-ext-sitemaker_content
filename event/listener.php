<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\content\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'init',
		);
	}

	public function init($event)
	{
		global $phpbb_container;

		$table_prefix = $phpbb_container->getParameter('core.table_prefix');
		define('CONTENT_TYPES_TABLE',	$table_prefix . 'content_types');
		define('CONTENT_FIELDS_TABLE',	$table_prefix . 'content_fields');
	}
}
