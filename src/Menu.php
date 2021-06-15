<?php

namespace Wptheme;

class Menu {

    public static function items( $location, $args = [] ) {
        $menuObject = self::getMenuByLocation( $location );
        $menuItems  = wp_get_nav_menu_items( $menuObject->name, $args );
        return self::menuItemsProcess( $menuItems );
    }

    private static function getMenuByLocation( $location ) {
        $menu = array_filter(
            THEMEMENUS,
            function( $item ) use ( $location ) {
                if ( $location == $item['id'] ) {
                    return $item;
                }
                return false;
            }
        );
        if ( [] === $menu ) {
            return [];
        }
        return wp_get_nav_menu_object( get_nav_menu_locations()[ $location ] );
    }

    private static function menuItemsProcess( $menuItems ) {
        $out = [];
        if ( empty( $menuItems ) ) {
            return false;
        }
        foreach ( $menuItems as $key => $item ) {
            $out[ $key ]          = $item;
            $out[ $key ]->current = self::isCurrent( $item );
            $out[ $key ]->domain  = self::domain( $item );
        }
        return $out;
    }

    private static function domain( $item ) {
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
