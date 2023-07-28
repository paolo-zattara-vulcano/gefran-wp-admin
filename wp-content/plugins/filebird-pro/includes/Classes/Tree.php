<?php
namespace FileBird\Classes;

defined( 'ABSPATH' ) || exit;

use FileBird\Controller\UserSettings;
use FileBird\Model\Folder as FolderModel;

class Tree {
	public static function getCount( $folder_id, $lang = null ) {
		global $wpdb;

		$select = "SELECT COUNT(*) FROM {$wpdb->posts} as posts WHERE ";
		$where  = array( "post_type = 'attachment'" );

		// With $folder_id == -1. We get all
		$where[] = "(posts.post_status = 'inherit' OR posts.post_status = 'private')";

		// with specific folder
		if ( $folder_id > 0 && ! apply_filters( 'fbv_speedup_get_count_query', false ) ) {
			$post__in = $wpdb->get_col( "SELECT `attachment_id` FROM {$wpdb->prefix}fbv_attachment_folder WHERE `folder_id` = " . (int) $folder_id );
			if ( count( $post__in ) == 0 ) {
				$post__in = array( 0 );
			}
			$where[] = '(ID IN (' . implode( ', ', $post__in ) . '))';
		} elseif ( $folder_id == 0 ) {
			return 0;//return 0 if this is uncategorized folder
		}

		$where = apply_filters( 'fbv_get_count_where_query', $where );

		$query = apply_filters( 'fbv_get_count_query', $select . implode( ' AND ', $where ), $folder_id, $lang );
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $query );
	}
	public static function getAllFoldersAndCount( $lang = null ) {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT fbva.folder_id, count(fbva.attachment_id) as count FROM {$wpdb->prefix}fbv_attachment_folder as fbva 
			INNER JOIN {$wpdb->prefix}fbv as fbv ON fbv.id = fbva.folder_id 
			INNER JOIN {$wpdb->posts} as posts ON fbva.attachment_id = posts.ID  
			WHERE (posts.post_status = 'inherit' OR posts.post_status = 'private') 
			AND (posts.post_type = 'attachment') 
			AND fbv.created_by = %d 
			GROUP BY fbva.folder_id",
			apply_filters( 'fbv_in_not_in_created_by', '0' )
		);
		$query = apply_filters( 'fbv_all_folders_and_count', $query, $lang );

		$results = $wpdb->get_results( $query );
		$return  = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $k => $v ) {
				$return[ $v->folder_id ] = $v->count;
			}
		}
		return $return;
	}
	public static function getFolders( $order_by = null, $flat = false ) {
		$userSettings         = UserSettings::getInstance()->settings;
		$folders_from_db      = FolderModel::allFolders( '*', null, $order_by );
		$folder_colors        = get_option( 'fbv_folder_colors', array() );
		$folder_default_color = $userSettings['theme']['themeColor'];
		$tree                 = array();

		$folders_from_db = self::prepareTreeData( $folders_from_db, $folder_colors, $folder_default_color );
		$groups          = self::groupByParent( $folders_from_db );
		if ( $flat === true ) {
			$tree = self::getFlatTreeByGroups( $groups, 0 );
		} else {
			$tree = self::getTreeByGroups( $groups, 0 );
		}
		return $tree;
	}
	public static function getFolder( $folder_id ) {
		$tree = self::getFolders();
		return Helpers::findFolder( $folder_id, $tree );
	}

	private static function groupByParent( $data ) {
		$group = array();
		if ( is_array( $data ) ) {
			foreach ( $data as $v ) {
				if ( ! isset( $group[ $v['li_attr']['data-parent'] ] ) ) {
					$group[ $v['li_attr']['data-parent'] ] = array();
				}
				$group[ $v['li_attr']['data-parent'] ][] = $v;
			}
		}
		return $group;
	}
	private static function getTreeByGroups( $groups, $parent = 0 ) {
		$tree = array();
		if ( isset( $groups[ $parent ] ) && is_array( $groups[ $parent ] ) ) {
			foreach ( $groups[ $parent ] as $node ) {
				$node['children'] = isset( $groups[ $node['id'] ] ) ? self::getTreeByGroups( $groups, $node['id'] ) : array();
				$tree[]           = $node;
			}
		}

		return $tree;
	}
	private static function getFlatTreeByGroups( $groups, $parent = 0, $level = 0 ) {
		$tree = array();
		if ( isset( $groups[ $parent ] ) && is_array( $groups[ $parent ] ) ) {
			foreach ( $groups[ $parent ] as $node ) {
				$node['text'] = str_repeat( '-', $level ) . $node['text'];
				$tree[]       = $node;
				if ( isset( $groups[ $node['id'] ] ) ) {
					$tree = array_merge( $tree, self::getFlatTreeByGroups( $groups, $node['id'], $level + 1 ) );
				}
			}
		}

		return $tree;
	}
	private static function prepareTreeData( $data, $folder_colors = array(), $folder_default_color = '#8f8f8f' ) {
		if ( ! is_array( $data ) ) {
			return array();
		}
		foreach ( $data as $k => $v ) {
			$data[ $k ] = array(
                'id'       => (int) $v->id,
				'children' => array(),
                'text'     => $v->name,
                'li_attr'  => array(
                    'data-count'  => 0,
                    'data-parent' => (int) $v->parent,
                    'real-count'  => 0,
                    'style'       => '--color: ' . ( isset( $folder_colors[ $v->id ] ) ? sanitize_hex_color( $folder_colors[ $v->id ] ) : $folder_default_color ),
                ),
            );
		}
		return $data;
	}
}
