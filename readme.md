# nikita.global WP theme
This package is to make wordpress theme development easier. I have created it for my needs, but it might be useful for you too.

## Basic installation
1. In installed and working wordpress create your theme folder in wp-content/themes. 
2. Run "composer require nikitaglobal/wptheme" in it.
3. Run "sh vendor/mycooltheme/wptheme/bin/mktheme" or copy vendor/mycooltheme/wptheme/toTheme files to your theme folder
4. Rename theme.php.sample to theme.php and edit it.

## theme.php
**THEMEPREFIX** is a basic constant. Can be almost any unique value of latin letters and numbers. Used for naming shortcodes, hooks, enqueuing styles and scripts.
**THEMEVERSION** is current version of your theme.
**THEMESCRIPTS** is an array of your js scripts used in theme. All paths are relative to your theme folder.
**THEMESTYLES** same for your css.
**THEMEMENUS** is an array of menu arrays, each menu is defined by id and label, see example below.
**TYPE_FIELDS** is an array which defines custom fields for each post types. "plans" and "reviews" are in the given example below. The syntax is almost like here [https://docs.carbonfields.net/learn/fields/usage.html]
**SETTINGS_FIELDS** is a similar array which defines basic theme settings. Like the contacts in header or footer.

Exampe:
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

Method Wptheme\Menu::items( 'topmenu' ) returns array of WP Objects for menu items for topmenu location.
Also the object has extra keys:
- **active** true/false if this item is the current item
- **domain** stripped domainname. https://somedomain.com/category/postname turns to somedomain. One of the application is obtainig popular service names, like youtube, instagram

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
		<a href="<?php echo esc_url( $item->url ); ?>" class="<?php echo esc_attr( $active ); ?>"><?php echo esc_html( $item->title ); ?></a>
		<?php
	}
	?>
</nav>
```


[![nikita.global](https://nikita.global/wp-content/themes/ngtheme/img/logo.svg)](https://nikita.global)
