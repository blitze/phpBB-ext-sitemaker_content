<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2019 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\template\extensions;

use Urodoz\Truncate\TruncateService;

class filters extends \Twig\Extension\AbstractExtension
{
	/**
	 * @inheritdoc
	 */
	public function getFilters()
	{
		return [
			new \Twig\TwigFilter('field', [$this, 'field_filter'], ['needs_context' => true]),
			new \Twig\TwigFilter('truncate', [$this, 'truncate_filter']),
		];
	}

	/**
	* @param array $context
	* @param array $items
	* @param string $field_type
	* @param string $loop_variable
	* @return string
	*/
	public function field_filter(array &$context, $items, $field_type, $loop_variable = '')
	{
		$found_fields = array_keys((array) $context['FIELD_TYPES'], $field_type);
		$field = array_shift($found_fields);

		if (isset($items[$field]))
		{
			if ($loop_variable)
			{
				$this->remove_from_context($context[$loop_variable], $field);
			}
			else
			{
				$this->remove_from_context($context, $field);
			}

			return $items[$field];
		}

		return '';
	}

	/**
	* @param string $content
	* @param int $max_chars
	* @param string $loop_variable
	* @return string
	*/
	public function truncate_filter($content, $max_chars = 60, $type = '')
	{
		if (!$max_chars)
		{
			return $content;
		}

		if ($type === 'html')
		{
			$truncator = new TruncateService();
			return $truncator->truncate($content, $max_chars);
		}

		return truncate_string($content, $max_chars, 255, false, '...');
	}

	/**
	 * @param array $data
	 * @param string $field
	 * @return void
	 */
	protected function remove_from_context(array &$data, $field)
	{
		unset($data['FIELDS']['all'][$field]);
		unset($data['FIELDS']['above'][$field]);
		unset($data['FIELDS']['body'][$field]);
		unset($data['FIELDS']['inline'][$field]);
		unset($data['FIELDS']['footer'][$field]);
	}
}
