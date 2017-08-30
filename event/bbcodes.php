<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class bbcodes implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.text_formatter_s9e_configure_after' => array('create_page_bbcode', 'create_tag_bbcode'),
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function create_page_bbcode(\phpbb\event\data $event)
	{
		// Get the BBCode configurator
		$configurator = $event['configurator'];

		// Let's unset any existing BBCode that might already exist
		unset($configurator->BBCodes['pagebreak']);
		unset($configurator->tags['pagebreak']);

		// Let's create the new BBCode
		$configurator->BBCodes->addCustom(
			'[pagebreak title={SIMPLETEXT;optional;postFilter=ucwords}]',
			'<p><!-- pagebreak --></p>' .
			'<xsl:if test="@title"><h4>{SIMPLETEXT}</h4><br /></xsl:if>'
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function create_tag_bbcode(\phpbb\event\data $event)
	{
		// Get the BBCode configurator
		$configurator = $event['configurator'];

		// Let's unset any existing BBCode that might already exist
		unset($configurator->BBCodes['tag']);
		unset($configurator->tags['tag']);

		// Let's create the new BBCode
		$configurator->BBCodes->addCustom(
			'[tag={IDENTIFIER}]{TEXT}[/tag]',
			"<!-- begin field -->\n" .
			"<div data-field=\"{IDENTIFIER}\">{TEXT}</div><br />\n" .
			"<!-- end field -->\n"
		);
	}
}
