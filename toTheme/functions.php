<?php
/**
 * Theme functions and definitions.
 *
 * @package NgTheme
 *
 * @codingStandardsIgnoreFile WordPress.Files.FileName.InvalidClassFileName
 */

require_once __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/theme.php';

/**
 * Main theme class.
 */
class NgTheme extends Wptheme\Theme {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}
}


new NgTheme();
