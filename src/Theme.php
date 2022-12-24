<?php

namespace Wptheme;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Theme {

	public function __construct() {
		add_theme_support( 'editor-style' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 2 );
		add_action( 'carbon_fields_register_fields', array( $this, 'action_carbon_fields_register_fields' ) );
		add_action( 'after_setup_theme', array( $this, 'action_after_setup_theme' ) );
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
				add_action( $action, array( $this, $method ) );
			}

			if ( 0 === strpos( $method, 'filter_' ) ) {
				$filter = str_replace( 'filter', THEMEPREFIX, $method );
				add_filter( $filter, array( $this, $method ) );
			}

			if ( 0 === strpos( $method, 'sc_' ) ) {
				$shortcode = str_replace( 'sc', THEMEPREFIX, $method );
				add_shortcode( $shortcode, array( $this, $method ) );
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
				array( 'jquery' ),
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
				array(),
				self::version(),
				'all'
			);
			wp_enqueue_style( self::sanitize( $scr ) );
		}
	}

	public function init_menu() {
		if ( ! defined( 'THEMEMENUS' ) || empty( THEMEMENUS ) ) {
			return;
		}
		foreach ( THEMEMENUS as $menu ) {
			register_nav_menu( $menu['id'], $menu['label'] );
		}
	}

	public function action_carbon_fields_register_fields() {
		if ( defined( 'TYPE_FIELDS' ) ) {
			foreach ( TYPE_FIELDS as $type_name => $type_arr ) {
				$fields = array();
				foreach ( $type_arr as $arr_field ) {
					$fields[] = $this->carbon_field_make( $arr_field );
				}
				Container::make( 'post_meta', __( 'Custom fields' ) )
				->where( 'post_type', '=', $type_name )
				->add_fields( $fields );
			}
		}
		if ( defined( 'SETTINGS_FIELDS' ) ) {
			$fields = array();
			foreach ( SETTINGS_FIELDS as $sets_item ) {
				$fields[] = $this->carbon_field_make( $sets_item );
			}
			Container::make( 'theme_options', __( 'Theme Options' ) )
			->add_fields( $fields );
		}
	}

	private function carbon_field_make( $arr_field ) {
		if ( isset( $arr_field[3] ) && $arr_field[3] ) {
			return Field::make( $arr_field[0], $arr_field[1], $arr_field[2] )->set_options( $arr_field[3] );
		}
		return Field::make( $arr_field[0], $arr_field[1], $arr_field[2] );
	}

	public static function tags( $pid = false ) {
		$pid  = $pid ? $pid : get_the_id();
		$out  = array();
		$tags = get_the_tags();
		if ( ! $tags || array() == $tags ) {
			return false;
		}
		foreach ( $tags as $tag ) {
			$out[] = array(
				'link' => get_tag_link( $tag ),
				'name' => $tag->name,
			);
		}
		return $out;
	}

	public static function __callStatic( $name, $arguments ) {
		$names     = preg_split( '/(?=[A-Z])/', $name );
		$className = 'Wptheme\\' . ucfirst( $names[0] );
		return $className::{$names[1]}( ...$arguments );
	}

	public static function Share( $network, $link = false ) {
		if ( '' == trim( $network ) ) {
			return false;
		}
		$link   = $link ? $link : self::getCurrentLink();
		$method = 'Share' . $network;
		if ( ! method_exists( __CLASS__, $method ) ) {
			return false;
		} else {
			return self::{$method}( $link );
		}
	}

	private static function shareFB( $link ) {
		return 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $link );
	}

	private static function shareVK( $link ) {
		return 'http://vk.com/share.php?url=' . urlencode( $link );
	}

	private static function shareTG( $link ) {
		return 'https://telegram.me/share/url?url=' . urlencode( $link );
	}

	public static function getCurrentLink() {
		global $wp;
		return add_query_arg( $_GET, home_url( $wp->request ) );
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
