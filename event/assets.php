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

class assets implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'blitze.sitemaker.admin_bar.set_assets' => 'set_swiper_assets',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function set_swiper_assets(\phpbb\event\data $event)
	{
		$event['assets'] = array_merge_recursive((array) $event['assets'], array(
			'js'   => array(
				'@blitze_content/vendor/swiper/dist/js/swiper.min.js',
				'@blitze_content/assets/blocks/swiper.min.js',
			),
			'css'   => array(
				'@blitze_content/assets/blocks/swiper.min.css',
			)
		));
	}
}
