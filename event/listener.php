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

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\language\language */
	protected $language;

	/* @var \blitze\content\services\types */
	protected $content_types;

	/* @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language				$language			Language object
	 * @param \blitze\content\services\types		$content_types		Content types object
	 * @param string								$php_ext			php file extension
	*/
	public function __construct(\phpbb\language\language $language, \blitze\content\services\types $content_types, $php_ext)
	{
		$this->language = $language;
		$this->content_types = $content_types;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_block_language',
			'core.make_jumpbox_modify_forum_list'	=> 'update_jumpbox',
			'core.viewonline_overwrite_location'	=> 'add_viewonline_location',
		);
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function load_block_language(\phpbb\event\data $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'blitze/content',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Remove content forums from forum jumpbox
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function update_jumpbox(\phpbb\event\data $event)
	{
		$event['rowset'] = array_diff_key($event['rowset'], $this->content_types->get_forum_types());
	}

	/**
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function add_viewonline_location(\phpbb\event\data $event)
	{
		if ($event['on_page'][1] == 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/content/') === 0)
		{
			$types = join('|', $this->content_types->get_forum_types());
			preg_match("/\/content\/($types)(\/[0-9]\/.*)?/is", $event['row']['session_page'], $match);

			if (sizeof($match))
			{
				$row = $this->content_types->get_type($match[1]);
				$lang = (!empty($match[2])) ? 'SITEMAKER_READING_TOPIC' : 'SITEMAKER_BROWSING_CONTENT';

				$event['location'] = $this->language->lang($lang, $row['content_langname']);
				$event['location_url'] = $event['row']['session_page'];
				unset($row);
			}
		}
	}
}
