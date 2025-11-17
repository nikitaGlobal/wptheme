<?php
/**
 * Theme class.
 *
 * @package Wptheme
 */

namespace Wptheme;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Main theme class.
 */
class Theme {

	/**
	 * Constructor.
	 */
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

	/**
	 * Initialize theme.
	 */
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

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		if ( empty( THEMESCRIPTS ) ) {
			return;
		}
		foreach ( THEMESCRIPTS as $script ) {
			$scr = self::script_path( $script );
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

	/**
	 * Enqueue styles.
	 */
	public function enqueue_styles() {
		if ( empty( THEMESTYLES ) ) {
			return;
		}
		foreach ( THEMESTYLES as $style ) {
			$scr = self::script_path( $style );
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

	/**
	 * Initialize menu.
	 */
	public function init_menu() {
		if ( ! THEMEMENUS ) {
			return;
		}
		foreach ( THEMEMENUS as $menu ) {
			register_nav_menu( $menu['id'], $menu['label'] );
		}
	}

	/**
	 * Register Carbon Fields.
	 */
	public function action_carbon_fields_register_fields() {
		// Поля для custom post types.
		foreach ( TYPE_FIELDS as $type_name => $type_arr ) {
			$fields = array();
			foreach ( $type_arr as $arr_field ) {
				// [0] - тип поля, [1] - название поля, [2] - метка поля.
				$fields[] = Field::make( $arr_field[0], $arr_field[1], $arr_field[2] );
			}
			Container::make( 'post_meta', __( 'Дополнительно' ) )
				->where( 'post_type', '=', $type_name )
				->add_fields( $fields );
		}
		// Поля настроек темы.
		$fields = array();
		foreach ( SETTINGS_FIELDS as $sets_item ) {
			$fields[] = Field::make( $sets_item[0], $sets_item[1], $sets_item[2] );
		}
		Container::make( 'theme_options', __( 'Настройки темы' ) )
			->add_fields( $fields );
	}

	/**
	 * Get tags.
	 *
	 * @param int|false $pid Post ID.
	 * @return array|false
	 */
	public static function tags( $pid = false ) {
		$pid  = $pid ? $pid : get_the_id();
		$out  = array();
		$tags = get_the_tags();
		if ( ! $tags || array() === $tags ) {
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

	/**
	 * Magic method for static calls.
	 *
	 * @param string $name Method name.
	 * @param array  $arguments Arguments.
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		// Проверяем, существует ли метод в текущем классе.
		if ( method_exists( __CLASS__, $name ) ) {
			return self::{$name}( ...$arguments );
		}
		$names = preg_split( '/(?=[A-Z])/', $name );
		if ( count( $names ) < 2 ) {
			return false;
		}
		$class_name = 'Wptheme\\' . ucfirst( $names[0] );
		if ( ! class_exists( $class_name ) ) {
			return false;
		}
		if ( ! method_exists( $class_name, $names[1] ) ) {
			return false;
		}
		return $class_name::{$names[1]}( ...$arguments );
	}

	/**
	 * After setup theme action.
	 */
	public function action_after_setup_theme() {
		\Carbon_Fields\Carbon_Fields::boot();
	}

	/**
	 * Get script path.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	public static function script_path( $file ) {
		if ( 0 !== strpos( $file, 'http' ) ) {
			return get_bloginfo( 'template_url' ) . '/' . preg_replace( '/^\//', '', $file );
		}

		return $file;
	}

	/**
	 * Get share URL.
	 *
	 * @param string       $network Network name.
	 * @param string|false $link    Link.
	 * @return string|false
	 */
	public static function share( $network, $link = false ) {
		if ( '' === trim( $network ) ) {
			return false;
		}
		$link   = $link ? $link : self::get_current_link();
		$method = 'share_' . strtolower( $network );
		if ( ! method_exists( __CLASS__, $method ) ) {
			return false;
		} else {
			return self::{$method}( $link );
		}
	}

	/**
	 * Get Facebook share URL.
	 *
	 * @param string $link Link.
	 * @return string
	 */
	private static function share_fb( $link ) {
		return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $link );
	}

	/**
	 * Get VK share URL.
	 *
	 * @param string $link Link.
	 * @return string
	 */
	private static function share_vk( $link ) {
		return 'http://vk.com/share.php?url=' . rawurlencode( $link );
	}

	/**
	 * Get Telegram share URL.
	 *
	 * @param string $link Link.
	 * @return string
	 */
	private static function share_tg( $link ) {
		return 'https://telegram.me/share/url?url=' . rawurlencode( $link );
	}

	/**
	 * Get current link.
	 *
	 * @return string
	 */
	public static function get_current_link() {
		global $wp;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return add_query_arg( $_GET, home_url( $wp->request ) );
	}

	/**
	 * Sanitize value.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	public static function sanitize( $value ) {
		return THEMEPREFIX . crc32( wp_json_encode( $value ) );
	}

	/**
	 * Get version.
	 *
	 * @return int|string
	 */
	public static function version() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			return time();
		}

		return THEMEVERSION;
	}
}
