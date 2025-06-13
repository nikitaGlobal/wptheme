<?php

namespace Wptheme;

/**
 * Menu maker class.
 */
class Menu {
	/**
	 * Get menu items by location.
	 *
	 * @param string $location location
	 * @param array  $args arguments
	 *
	 * @return false|array
	 */
	public static function items( string $location, $args = array() ) {
		$menuObject = self::get_menu_by_location( $location );
		if ( empty( $menuObject ) ) {
			return false;
		}
		$menuItems = wp_get_nav_menu_items( $menuObject->name, $args );
		$result    = self::menu_items_nested( self::menu_items_process( $menuItems ) );
		return $result;
	}

	/**
	 * Get menu object by location.
	 *
	 * @param string $location location
	 *
	 * @return false|array
	 */
	private static function get_menu_by_location( $location ) {
		$menu = array_filter(
			THEMEMENUS,
			function ( $item ) use ( $location ) {
				if ( $location == $item['id'] ) {
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
	 * @param array $menuItems menu items
	 *
	 * @return array
	 */
	private static function menu_items_process( $menuItems ) {
		$out = array();
		if ( empty( $menuItems ) ) {
			return false;
		}
		foreach ( $menuItems as $key => $item ) {
			$out[ $key ]          = $item;
			$out[ $key ]->current = self::isCurrent( $item );
			$out[ $key ]->domain  = self::getDomain( $item );
		}
		return $out;
	}

	private static function menu_items_nested( $menuItems, $parentId = 0 ) {
		$menuItemsCopy = $menuItems;
		foreach ( $menuItems as $key => $item ) {
			if ( $parentId !== (int) $item->menu_item_parent ) {
				unset( $menuItems[ $key ] );
				continue;
			}
			$children                    = self::menu_items_nested( $menuItemsCopy, $item->ID );
			$menuItems[ $key ]->children = $children;
		}
		return $menuItems;
	}

	/**
	 * Get domain from item.
	 *
	 * @param object $item item
	 *
	 * @return string|false
	 */
	private static function getDomain( $item ) {
		if ( 0 !== strpos( $item->url, 'http' ) ) {
			return false;
		}
		$domain = str_replace( '//www.', '//', $item->url );
		return preg_replace( '/[^\:]*\:\/\/([^\.]*)\.[^$]*$/', '$1', $domain );
	}

	private static function isCurrent( $item ) {
		return $item->object_id == get_queried_object_id();
	}
}
