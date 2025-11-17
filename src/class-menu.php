<?php
/**
 * Menu class.
 *
 * @package Wptheme
 */

namespace Wptheme;

/**
 * Menu maker class.
 */
class Menu {

	/**
	 * Get menu items by location.
	 *
	 * @param string $location Location.
	 * @param array  $args     Arguments.
	 *
	 * @return false|array
	 */
	public static function items( string $location, $args = array() ) {
		$menu_object = self::get_menu_by_location( $location );
		if ( empty( $menu_object ) ) {
			return false;
		}
		$menu_items = wp_get_nav_menu_items( $menu_object->name, $args );
		$result     = self::menu_items_nested( self::menu_items_process( $menu_items ) );
		return $result;
	}

	/**
	 * Get menu object by location.
	 *
	 * @param string $location Location.
	 *
	 * @return false|array
	 */
	private static function get_menu_by_location( $location ) {
		$menu = array_filter(
			THEMEMENUS,
			function ( $item ) use ( $location ) {
				if ( $item['id'] === $location ) {
					return $item;
				}
				return false;
			}
		);
		if ( array() === $menu ) {
			return array();
		}
		return wp_get_nav_menu_object( get_nav_menu_locations()[ $location ] );
	}

	/**
	 * Process menu items.
	 *
	 * @param array $menu_items Menu items.
	 *
	 * @return array
	 */
	private static function menu_items_process( $menu_items ) {
		$out = array();
		if ( empty( $menu_items ) ) {
			return false;
		}
		foreach ( $menu_items as $key => $item ) {
			$out[ $key ]          = $item;
			$out[ $key ]->current = self::is_current( $item );
			$out[ $key ]->domain  = self::get_domain( $item );
		}
		return $out;
	}

	/**
	 * Get nested menu items.
	 *
	 * @param array $menu_items Menu items.
	 * @param int   $parent_id  Parent ID.
	 *
	 * @return array
	 */
	private static function menu_items_nested( $menu_items, $parent_id = 0 ) {
		$menu_items_copy = $menu_items;
		foreach ( $menu_items as $key => $item ) {
			if ( (int) $item->menu_item_parent !== $parent_id ) {
				unset( $menu_items[ $key ] );
				continue;
			}
			$children                     = self::menu_items_nested( $menu_items_copy, $item->ID );
			$menu_items[ $key ]->children = $children;
		}
		return $menu_items;
	}

	/**
	 * Get domain from item.
	 *
	 * @param object $item Item.
	 *
	 * @return string|false
	 */
	private static function get_domain( $item ) {
		if ( 0 !== strpos( $item->url, 'http' ) ) {
			return false;
		}
		$domain = str_replace( '//www.', '//', $item->url );
		return preg_replace( '/[^\:]*\:\/\/([^\.]*)\.[^$]*$/', '$1', $domain );
	}

	/**
	 * Check if item is current.
	 *
	 * @param object $item Item.
	 *
	 * @return bool
	 */
	private static function is_current( $item ) {
		return get_queried_object_id() === (int) $item->object_id;
	}
}
