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
		$menuObject = self::getMenuByLocation( $location );
		if ( empty( $menuObject ) ) {
			return false;
		}
		$menuItems = wp_get_nav_menu_items( $menuObject->name, $args );
		return self::menuItemsProcess( $menuItems );
	}

	/**
	 * Get menu object by location.
	 *
	 * @param string $location location
	 *
	 * @return false|array
	 */
	private static function getMenuByLocation( $location ) {
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
	private static function menuItemsProcess( $menuItems ) {
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
