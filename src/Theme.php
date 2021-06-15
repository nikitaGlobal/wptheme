<?php

namespace Wptheme;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Theme {

    public function __construct() {
        add_theme_support( 'editor-style' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'menus' );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 2 );
        $this->init();
    }

    public function init() {
        $methods = get_class_methods( $this );
        if ( empty( $methods ) ) {
            return;
        }
        foreach ( $methods as $method ) {
            if ( 0 === strpos( $method, 'init_' ) ) {
                $this->{$method}();
            }

            if ( 0 === strpos( $method, 'action_' ) ) {
                $action = str_replace( 'action', THEMEPREFIX, $method );
                add_action( $action, [ $this, $method ] );
            }

            if ( 0 === strpos( $method, 'filter_' ) ) {
                $filter = str_replace( 'filter', THEMEPREFIX, $method );
                add_filter( $filter, [ $this, $method ] );
            }

            if ( 0 === strpos( $method, 'sc_' ) ) {
                $shortcode = str_replace( 'sc', THEMEPREFIX, $method );
                add_shortcode( $shortcode, [ $this, $method ] );
            }
        }
    }

    public function enqueue_scripts() {
        if ( empty( THEMESCRIPTS ) ) {
            return;
        }
        foreach ( THEMESCRIPTS as $script ) {
            $scr = self::scriptPath( $script );
            wp_register_script(
                self::sanitize( $scr ),
                $scr,
                [ 'jquery' ],
                self::version(),
                true
            );
                wp_enqueue_script( self::sanitize( $scr ) );
        }
    }

    public function enqueue_styles() {
        if ( empty( THEMESTYLES ) ) {
            return;
        }
        foreach ( THEMESTYLES as $style ) {
            $scr = self::scriptPath( $style );
            wp_register_style(
                self::sanitize( $scr ),
                $scr,
                [],
                self::version(),
                'all'
            );
                wp_enqueue_style( self::sanitize( $scr ) );
        }
    }

    public function init_menu() {
        if ( ! THEMEMENUS ) {
            return;
        }
        foreach ( THEMEMENUS as $menu ) {
            register_nav_menu( $menu['id'], $menu['label'] );
        }
    }

    public function action_carbon_fields_register_fields() {
          Container::make( 'theme_options', __( 'Theme Options' ) )
        ->add_fields(
            [
                Field::make( 'text', 'crb_text', 'Text Field' ),
            ]
        );
    }

    public static function __callStatic( $name, $arguments ) {
        $names     = preg_split( '/(?=[A-Z])/', $name );
        $className = 'Wptheme\\'.ucfirst($names[0]);
        return $className::{$names[1]}( ...$arguments );
    }

    public function action_after_setup_theme() {
         \Carbon_Fields\Carbon_Fields::boot();
    }

    public static function scriptPath( $file ) {
        if ( 0 !== strpos( $file, 'http' ) ) {
            return get_bloginfo( 'template_url' ) . '/' . preg_replace( '/^\//', '', $file );
        }

        return $file;
    }

    public static function sanitize( $value ) {
        return THEMEPREFIX . crc32( wp_json_encode( $value ) );
    }

    public static function version() {
        if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
            return time();
        }

        return THEMEVERSION;
    }
}
