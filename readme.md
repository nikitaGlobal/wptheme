# nikita.global WP theme

This package is to make WordPress theme development easier. I have created it for my needs, but it might be useful for you too.

**Features:**
- WordPress Coding Standards compliant
- Carbon Fields integration for custom fields
- Automatic menu handling with nested items support
- Automatic hooks, filters, and shortcodes registration
- Social sharing functionality
- PSR-4 autoloading support

## Basic installation

1. In installed and working WordPress create your theme folder in `wp-content/themes`.
2. Run `composer require nikitaglobal/wptheme` in it.
3. Run `sh vendor/nikitaglobal/wptheme/bin/mktheme` or copy `vendor/nikitaglobal/wptheme/toTheme` files to your theme folder.
4. Rename `theme.php.sample` to `theme.php` and edit it.

## theme.php Configuration

The `theme.php` file contains all theme configuration constants:

- **THEMEPREFIX** - Basic constant. Can be almost any unique value of latin letters and numbers. Used for naming shortcodes, hooks, enqueuing styles and scripts.
- **THEMEVERSION** - Current version of your theme.
- **THEMESCRIPTS** - Array of your JavaScript scripts used in theme. All paths are relative to your theme folder or absolute URI.
- **THEMESTYLES** - Array of your CSS files. If style is prefixed with `@`, it will be inlined.
- **THEMEMENUS** - Array of menu arrays, each menu is defined by `id` and `label`, see example below.
- **TYPE_FIELDS** - Array which defines custom fields for each post type. "plans" and "reviews" are in the given example below. The syntax is almost like [Carbon Fields documentation](https://docs.carbonfields.net/learn/fields/usage.html).
- **SETTINGS_FIELDS** - Array which defines basic theme settings. Like the contacts in header or footer.
- **TEMPLATE_FIELDS** - Array which defines custom fields for theme templates. Like price for product page.

Example:
```php
<?php
define( 'THEMEPREFIX', 'mycooltheme' );
define( 'THEMEVERSION', '1.0' );
define( 'THEMESCRIPTS', array( 'assets/js/app.js', 'assets/js/slider.js' ) );
define( 'THEMESTYLES', array( 'assets/css/main.css' ) );
define(
	'THEMEMENUS',
	array(
		array(
			'id'    => 'topmenu',
			'label' => __( 'Top menu', 'mycooltheme' ),
		),
		array(
			'id'    => 'socialmenu',
			'label' => __( 'Social networks', 'mycooltheme' ),
		),
	)
);
define(
	'TYPE_FIELDS',
	array(
		'plans'           => array(
			array( 'text', THEMEPREFIX . '_price', __( 'Price', 'mycooltheme' ) ),
			array( 'select', THEMEPREFIX . '_stars', __( 'Stars', 'mycooltheme' ), array( 1,2,3,4,5 ) ),
			array( 'select', THEMEPREFIX . '_plan', __( 'Plan', 'mycooltheme' ), 
				array( 'month' => __('Month', 'mycooltheme'), 'year' => __('Year', 'mycooltheme') ) ),
		),
		'reviews'           => array(
			array( 'richtext', THEMEPREFIX . '_hotel', __( 'About hotel', 'mycooltheme' ) ),
		),
	)
);
define(
	'TEMPLATE_FIELDS',
	array(
		'tpl-product.php' => array(
			array( 'text', THEMEPREFIX . '_price', __( 'Product price', 'mycooltheme' ) ),
		)
	)
);
define(
	'SETTINGS_FIELDS',
	array(
		array( 'text', THEMEPREFIX . '_phone', __( 'Phone', 'mycooltheme' ) ),
		array( 'text', THEMEPREFIX . '_email', __( 'E-mail', 'mycooltheme' ) ),
		array( 'text', THEMEPREFIX . '_instagram', __( 'Instagram', 'mycooltheme' ) ),
		array( 'text', THEMEPREFIX . '_linkedin', __( 'Linkedin', 'mycooltheme' ) ),
    )
);
```

## Menus

Method `Wptheme\Menu::items( 'topmenu' )` returns array of WP Objects for menu items for topmenu location.
Also the object has extra keys:
- **current** true/false if this item is the current item
- **domain** stripped domainname. https://somedomain.com/category/postname turns to somedomain. One of the application is obtaining popular service names, like youtube, instagram
- **children** array of child menu items with the same structure as parent items

Example:
```php
<?php $items = Wptheme\Menu::items( 'topmenu' );
if ( empty( $items ) ) {
	return;
}
?>
<nav class="main-nav">
	<?php
	foreach ( $items as $item ) {
		$active = $item->current ? 'active' : '';
		?>
		<div class="menu-item">
			<a href="<?php echo esc_url( $item->url ); ?>" class="<?php echo esc_attr( $active ); ?>"><?php echo esc_html( $item->title ); ?></a>
			<?php if ( ! empty( $item->children ) ) : ?>
				<div class="submenu">
					<?php foreach ( $item->children as $child ) : ?>
						<a href="<?php echo esc_url( $child->url ); ?>" class="<?php echo esc_attr( $child->current ? 'active' : '' ); ?>"><?php echo esc_html( $child->title ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
	?>
</nav>
```

## Tags

### For single post

`Wptheme\Theme::tags( $pid = false )` - Returns array of tags for the current post or specified post ID. Each tag contains:
- **link** - URL to tag archive
- **name** - Tag name

Returns `false` if no tags found.

Example:
```php
<?php $tags = Wptheme\Theme::tags(); ?>
<?php if ( $tags ) : ?>
	<ul>
		<?php foreach ( $tags as $tag ) : ?>
			<li><a href="<?php echo esc_url( $tag['link'] ); ?>"><?php echo esc_html( $tag['name'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
```

## Social Sharing

`Wptheme\Theme::share( $network, $link = false )` - Get share URL for social networks.

Supported networks: `fb` (Facebook), `vk` (VKontakte), `tg` (Telegram).

Example:
```php
<?php $share_url = Wptheme\Theme::share( 'fb' ); ?>
<a href="<?php echo esc_url( $share_url ); ?>">Share on Facebook</a>
```

## Utility Methods

### Get Current Link
`Wptheme\Theme::get_current_link()` - Returns current page URL with query parameters.

### Script Path
`Wptheme\Theme::script_path( $file )` - Converts relative file path to absolute URL. If path starts with `http`, returns as is.

### Version
`Wptheme\Theme::version()` - Returns theme version. In debug mode (WP_DEBUG), returns current timestamp for cache busting.

## Hooks and Filters

The theme automatically registers hooks and filters based on method naming conventions in your `NgTheme` class:

- **init_** prefix - Methods are called automatically during theme initialization
  ```php
  public function init_menu() {
      // This will be called automatically
  }
  ```

- **action_** prefix - Methods are registered as WordPress actions
  ```php
  public function action_custom_hook() {
      // Registered as: {THEMEPREFIX}_custom_hook
  }
  ```

- **filter_** prefix - Methods are registered as WordPress filters
  ```php
  public function filter_custom_content( $content ) {
      // Registered as: {THEMEPREFIX}_custom_content
      return $content;
  }
  ```

- **sc_** prefix - Methods are registered as shortcodes
  ```php
  public function sc_my_shortcode( $atts ) {
      // Registered as: [{THEMEPREFIX}_my_shortcode]
      return 'Shortcode output';
  }
  ```

[![nikita.global](https://nikita.global/wp-content/themes/ngtheme/img/logo.svg)](https://nikita.global)

## Changelog

### [v2.19] - 2025-11-17
#### Changed
- readme.md updated with comprehensive documentation
- Added documentation for all utility methods
- Added documentation for hooks and filters system
- Fixed menu documentation (active → current)
- Added social sharing documentation

### [v2.18] - 2025-11-17
#### Changed
- Tags fixed, code linted
- Code refactored to comply with WordPress Coding Standards
- All methods and variables renamed to snake_case format
- Replaced loose comparisons (==) with strict comparisons (===)
- Replaced urlencode() with rawurlencode()
- Added Yoda conditions where required
- Added comprehensive PHPDoc comments

### [v2.17] - 2025-06-14
#### Added
- Recursive menu items support
- Menu children items functionality

### [v2.16] - 2025-06-08
#### Fixed
- Theme options bug fixed
#### Added
- Admin styles
#### Changed
- Code linted

### [v2.15] - 2025-06-06
#### Changed
- Styles updated
- Code linted

### [v2.14] - 2025-05-31
#### Changed
- readme.md updated
- theme.php slightly updated

### [v2.13] - 2023-05-18
#### Fixed
- Removed vendor folder from repository
- Added vendor folder to .gitignore

### [v2.12] - 2023-01-13
#### Added
- Template fields feature (TEMPLATE_FIELDS constant)

### [v2.11] - 2022-12-25
- Version release

### [v2.1] - 2022-12-25
#### Fixed
- Typo in readme.md

### [v2.0] - 2022-12-25
#### Added
- Documentation and comments
- Improved code structure

#### Fixed
- Undefined THEMEMENUS constant issue

### [1.9] - 2022-04-21
- Version release

### [1.8] - 2022-04-21
#### Changed
- Flat Carbon Fields structure

### [1.7] - 2022-02-07
- Version release

### [1.6] - 2021-06-15
- Version release

### [1.5] - 2020
#### Added
- Menu functionality improvements

### [1.4] - 2020
- Version release

### [1.3] - 2020
- Version release

### [v1.2] - 2020
#### Changed
- Composer.json updates

### [v1.1] - 2020
#### Changed
- Composer.json updates

### [v1.0] - 2020
#### Added
- Initial release
- Basic theme structure
- Carbon Fields integration
- Menu system
- Script and style enqueuing
- Theme settings functionality


