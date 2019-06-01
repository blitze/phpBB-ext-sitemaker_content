<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\services\form\field;

class location extends base
{
	/** @var \blitze\sitemaker\services\util */
	protected $util;

	/** @var string */
	protected $google_api_key;

	/** @var string */
	protected $session_id;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                  	$language       	Language object
	 * @param \phpbb\request\request_interface				$request			Request object
	 * @param \blitze\sitemaker\services\template			$ptemplate			Sitemaker template object
	 * @param \phpbb\config\config							$config				Config object
	 * @param \phpbb\user									$user				User object
	 * @param \blitze\sitemaker\services\util				$util       		Sitemaker utility object
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\request\request_interface $request, \blitze\sitemaker\services\template $ptemplate, \phpbb\config\config $config, \phpbb\user $user, \blitze\sitemaker\services\util $util)
	{
		parent::__construct($language, $request, $ptemplate);

		$this->util = $util;
		$this->google_api_key = $config['google_api_key'];
		$this->session_id = $user->session_id;
	}

	/**
	 * @inheritdoc
	 */
	public function get_name()
	{
		return 'location';
	}

	/**
	 * @inheritdoc
	 */
	public function get_default_props()
	{
		return array(
			'disp_type'		=> 'address',
			'map_types'		=> '',
			'map_width'		=> '100%',
			'map_height'	=> 400,
			'map_zoom'		=> 0,
			'session_id'	=> $this->session_id,
		);
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function get_field_value(array $data)
	{
		$fields = ['map_type', 'place', 'address', 'zoom', 'latitude', 'longitude'];
		$field_value = $this->ensure_is_array($data['field_value']);
		$field_value = array_pad(array_map('trim', $field_value), count($fields), 0);

		return array_combine($fields, $field_value);
	}

	/**
	 * @inheritdoc
	 */
	public function display_field(array $data, array $topic_data, $view_mode)
	{
		$callable = 'display_' . $data['field_props']['disp_type'];

		if (!$data['field_value'])
		{
			return '';
		}

		return $this->$callable($data);
	}

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function get_submitted_value(array $data)
	{
		$value = $this->get_field_value($data);
		$value = $this->request->variable($data['field_name'], $value, true);

		return array_filter($value);
	}

	/**
	 * @inheritdoc
	 */
	public function show_form_field(array &$data)
	{
		$this->util->add_assets(array(
			'js'	=> array(
				100 => '@blitze_content/assets/fields/form.min.js',
				101 => '//maps.googleapis.com/maps/api/js?key=' . $this->google_api_key . '&libraries=places&callback=initMap&language=' . $this->language->get_used_language() . '" async defer charset="UTF-8',
			)
		));

		$data['show_input'] = true;

		return parent::show_form_field($data);
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function display_address(array $data)
	{
		return $this->get_location_title($data['field_value']['place'], $data['field_value']['address']) . $data['field_value']['address'];
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function display_coordinates(array $data)
	{
		return $this->get_location_title($data['field_value']['place'], $data['field_value']['address']) .
			$data['field_value']['latitude'] . ', ' . $data['field_value']['longitude'];
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function display_active_map(array $data)
	{
		// do not include assets on preview page as form already handles this
		if (!$this->request->is_set_post('cp'))
		{
			$this->util->add_assets(array(
				'js'	=> array(
					'//maps.googleapis.com/maps/api/js?key=' . $this->google_api_key . '&callback=initMap&language=' . $this->language->get_used_language() . '" async defer charset="UTF-8',
					'@blitze_content/assets/fields/display.min.js',
				)
			));
		}

		$this->ptemplate->assign_vars($data);
		return $this->ptemplate->render_view('blitze/content', "fields/location.html", 'location_field');
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected function display_static_map(array $data)
	{
		$info = $data['field_value'];
		$settings = $data['field_props'];
		$map_types = $settings['options'] ?: ['roadmap'];
		$coordinates = $info['latitude'] . ',' . $info['longitude'];

		$params = array(
			'center'	=> $coordinates,
			'zoom'		=> $settings['map_zoom'] ?: $info['zoom'],
			'size'		=> (int) $settings['map_width'] . 'x' . (int) $settings['map_height'],
			'maptype'	=> $info['map_type'] ?: $map_types[0],
			'markers'	=> $coordinates,
			'key'		=> $this->google_api_key,
		);

		return '<img src="https://maps.googleapis.com/maps/api/staticmap?' . http_build_query($params) . '" alt="' . $info['address'] . '" title="' . $info['place'] . '" />';
	}

	/**
	 * @param string $place
	 * @param string $address
	 * @return string
	 */
	protected function get_location_title($place, $address)
	{
		return (strpos($address, $place) === false) ? '<strong>' . $place . '</strong><br />' : '';
	}
}
