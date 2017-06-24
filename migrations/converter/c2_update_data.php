<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\migrations\converter;

use Cocur\Slugify\Slugify;

class c2_update_data extends \phpbb\db\migration\migration
{
	/**
	 * Skip this migration if the content types table does not exist
	 *
	 * @return bool True to skip this migration, false to run it
	 * @access public
	 *
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'content_types');
	}
	*/

	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array(
			'\blitze\content\migrations\v20x\m1_initial_schema',
			'\blitze\content\migrations\v20x\m2_initial_data',
		);
	}

	public function update_data()
	{
		if (!$this->db_tools->sql_table_exists($this->table_prefix . 'content_types'))
		{
			return array();
		}

		include($this->phpbb_root_path . 'includes/bbcode.' . $this->php_ext);
		include($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);

		$slugify = new Slugify();
		$message_parser = new \parse_message();
		$message_parser->mode = 'post';
		$message_parser->bbcode_init();

		$last_topic_id = 0;
		$return_data = array();
		$topic_tags = array();
		$display_maps = array(
			0 => 'blitze.content.view.blog',
			1 => 'blitze.content.view.portal',
			2 => 'blitze.content.view.tiles',
			3 => 'blitze.content.view.tiles'
		);

		$sql = 'SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_tag, p.post_id, p.post_text, p.bbcode_uid, p.enable_bbcode, p.enable_smilies
			FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
			WHERE p.post_id = t.topic_first_post_id
				AND t.topic_tag <> ''
				AND t.forum_id = f.forum_id
				AND f.parent_id = " . (int) $this->config['content_forum_id'] . '
			ORDER BY t.topic_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_id	= (int) $row['forum_id'];
			$topic_id	= (int) $row['topic_id'];
			$post_id	= (int) $row['post_id'];

			// Convert bbcodes back to their normal form
			if ($row['enable_bbcode'])
			{
				decode_message($row['post_text'], $row['bbcode_uid']);

				$message_parser->message = $row['post_text'];

				$message_parser->prepare_bbcodes();
				$message_parser->parse_bbcode();

				$bitfield = $message_parser->bbcode_bitfield;

				$this->db->sql_query('UPDATE ' . POSTS_TABLE . " SET bbcode_bitfield = '" . $this->db->sql_escape($bitfield) . "' WHERE post_id = " . $post_id);
			}
			else
			{
				$this->db->sql_query('UPDATE ' . POSTS_TABLE . " SET bbcode_bitfield = '' WHERE post_id = " . $post_id);
			}

			if ($topic_id != $last_topic_id)
			{
				$last_topic_id = $topic_id;
				$topic_tags[$forum_id] = $row['topic_tag'];
				$slug = $slugify->slugify($row['topic_title']);

				$this->db->sql_query('UPDATE ' . TOPICS_TABLE . " SET topic_slug = '$slug' WHERE topic_id = " . $topic_id);
			}
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT c.*, f.forum_name
			FROM ' . $this->table_prefix . 'content_types c, ' . $this->table_prefix . 'forums f
			WHERE f.forum_id = c.forum_id
				AND ' . $this->db->sql_in_set('c.forum_id', array_keys($topic_tags));
		$result = $this->sql_query($sql);

		$content_id = 0;
		$content_types = $content_fields = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_id = (int) $row['forum_id'];
			$type_name = $topic_tags[$forum_id];

			$return_data[] = array('permission.remove', array('u_content_view_' . $type_name));
			$return_data[] = array('permission.remove', array('u_content_post_' . $type_name));
			$return_data[] = array('permission.remove', array('m_content_manage_' . $type_name));

			$content_types[] = array(
				'content_id'			=> ++$content_id,
				'forum_id'				=> $forum_id,
				'content_name'			=> $type_name,
				'content_langname'		=> $row['forum_name'],
				'content_colour'		=> substr(md5($type_name), 0, 6),
				'content_desc'			=> $row['content_desc'],
				'content_desc_bitfield'	=> $row['content_desc_bitfield'],
				'content_desc_options'	=> $row['content_desc_options'],
				'content_desc_uid'		=> $row['content_desc_uid'],
				'content_view'			=> $display_maps[$row['display_type']],
				'content_view_settings'	=> '',
				'req_approval'			=> (bool) $row['req_approval'],
				'allow_comments'		=> (bool) $row['allow_comments'],
				'show_poster_info'		=> (bool) $row['show_poster_info'],
				'show_poster_contents'	=> (bool) $row['show_poster_contents'],
				'show_pagination'		=> (bool) $row['show_pagination'],
				'items_per_page'		=> (int) $row['items_per_page'],
				'summary_tpl'			=> '',
				'detail_tpl'			=> '',
			);

			$fields = unserialize($row['content_fields']);
			$fields = array_values($fields);

			for ($i = 0, $size = sizeof($fields); $i < $size; $i++)
			{
				$field_props = '';
				$field_type = $fields[$i]['name'];

				if ($field_type == 'content')
				{
					$field_type = 'textarea';
					$field_props = json_encode(array(
						'max_chars' => $row['char_limit'],
						'rows'		=> 25,
						'editor'	=> true,
					));
				}
				else if ($field_type == 'summary')
				{
					$field_type = 'textarea';
					$field_props = json_encode(array(
						'max_chars' => $row['char_limit'],
						'rows'		=> 5,
						'editor'	=> true,
					));
				}

				$content_fields[] = array(
					'content_id'			=> $content_id,
					'field_name'			=> strtolower(str_replace(' ', '_', $fields[$i]['label'])),
					'field_label'			=> $fields[$i]['label'],
					'field_explain'			=> '',
					'field_type'			=> $field_type,
					'field_props	'		=> $field_props,
					'field_mod_only'		=> !$fields[$i]['input'],
					'field_required'		=> $fields[$i]['required'],
					'field_summary_show'	=> $fields[$i]['teaser'],
					'field_detail_show'		=> $fields[$i]['body'],
					'field_order'			=> $i
				);
			}
		}
		$this->db->sql_freeresult($result);

		$this->import_data($content_types, 'sm_content_types');
		$this->import_data($content_fields, 'sm_content_fields');

		$return_data[] = array('config.remove', array('content_forum_id'));

		return $return_data;
	}

	public function import_data($import_data, $table)
	{
		// If we have data to import, let's go!! :)
		if (!empty($import_data))
		{
			// Load the insert buffer class to perform a buffered multi insert
			$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . $table);
			// Insert imported data to our table
			foreach ($import_data as $data)
			{
				$insert_buffer->insert($data);
			}
			// Flush the buffer
			$insert_buffer->flush();
		}
	}
}
