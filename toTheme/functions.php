<?php
require_once __DIR__ . '/vendor/autoload.php';

class NgTheme extends Wptheme\Theme {
    function __construct() {
        parent::__construct();
    }
}


new NgTheme();
