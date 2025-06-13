<?php

namespace Wptheme;

use function wp_get_nav_menu_items;
use function wp_get_nav_menu_object;
use function get_nav_menu_locations;
use function get_queried_object_id;

if ( ! defined( 'THEMEMENUS' ) ) {
	define( 'THEMEMENUS', array() );
}

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
		return self::menu_items_process( $menuItems );
	}

	private static function menu_items_nested( $menuItems, $parentId = 0 ) {
		$out = array();
		foreach ( $menuItems as $item ) {
			if ( $item->menu_item_parent === $parentId ) {
				$item->children = self::menu_items_nested( $menuItems, $item->ID );
				$out[]          = $item;
			}
		}
		return ! empty( $out ) ? $out : false;
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
				if ( $location === $item['id'] ) {
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
			$out[ $key ]->current = self::is_current( $item );
			$out[ $key ]->domain  = self::get_domain( $item );
		}
		$out = self::menu_items_nested( $out );
		return $out;
	}

	/**
	 * Get domain from item.
	 *
	 * @param object $item item
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

	private static function is_current( $item ) {
		return get_queried_object_id() === $item->object_id;
	}
}
