<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */

/**
 * Get content types for block config
 */
function select_content_type($content_types, $type)
{
	global $user;

	$html = '';
	foreach ($content_types as $value => $title)
	{
		$selected = ($type == $value) ? ' selected="selected"' : '';
		$html .= '<option value="' . $value . '"' . $selected . ' data-toggle-setting="#fields-col-' . $value . '">' . $title . '</option>';
	}

	return $html;
}
