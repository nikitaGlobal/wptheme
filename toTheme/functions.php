<?php
require_once __DIR__ . '/vendor/autoload.php';

define('themeprefix', 'THEMEPREFIXHERE');

class NgTheme extends Wptheme\Theme {
    function __construct() {
        parent::__construct();
    }
}


new NgTheme();
