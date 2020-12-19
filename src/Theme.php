<?php

namespace Wptheme;

class Theme
{
    public function __construct()
    {
        add_theme_support("editor-style");
        add_theme_support("post-thumbnails");
        $this->init();
    }

    public function init()
    {
        if (isset($this->initSkip)) {
            return;
        }
        $this->initSkip = true;
        $methods = get_class_methods($this);
        if (empty($methods)) {
            return;
        }
        foreach ($methods as $method) {
            if (strpos($method, 'init') === 0) {
                $this->{$method}();
            }
            if (strpos($method, 'filter') === 0) {
                $filter = str_replace('filter', '', $method);
                add_filter($filter, array($this, $method));
            }
            if (strpos($method, 'sc') === 0) {
                $shortcode = str_replace('sc', self::prefix(), $method);
                add_shortcode($shortcode, array($this, $method));
            }
        }
    }

    public static function version()
    {
        if (defined('WP_DEBUG') && WP_DEBUG == true) {
            return time();
        }

        return THEMEVERSION;
    }
}
