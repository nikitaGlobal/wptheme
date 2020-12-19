<?php

namespace Wptheme;

class Theme {



    public function __construct() {
        add_theme_support( 'editor-style' );
        add_theme_support( 'post-thumbnails' );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 2 );
        $this->init();
    }

    public function init() {
        if ( isset( $this->initSkip ) ) {
            return;
        }
        $this->initSkip = true;
        $methods        = get_class_methods( $this );
        if ( empty( $methods ) ) {
            return;
        }
        foreach ( $methods as $method ) {
            if ( strpos( $method, 'init' ) === 0 ) {
                $this->{$method}();
            }
            if ( strpos( $method, 'filter' ) === 0 ) {
                $filter = str_replace( 'filter', '', $method );
                add_filter( $filter, [ $this, $method ] );
            }
            if ( strpos( $method, 'sc' ) === 0 ) {
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

    public static function scriptPath( $file ) {
        if ( strpos( $file, 'http' ) !== 0 ) {
            return get_bloginfo( 'template_url' ) . '/' . $file;
        }

        return $file;
    }
    public static function sanitize( $value ) {
        return THEMEPREFIX . crc32( wp_json_encode( $value ) );
    }

    public static function version() {
        if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) {
            return time()load_stenqueue
        }

        return THEMEVERSION;
    }
}
