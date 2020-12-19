<?php
require_once __DIR__ . '/vendor/autoload.php';

define('THEMEPREFIX', 'THEMEPREFIXHERE');
define('THEMEVERSION', '1.0');

class NgTheme extends Wptheme\Theme {
    function __construct() {
        parent::__construct();
    }
}


new NgTheme();
