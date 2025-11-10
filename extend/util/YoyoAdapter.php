<?php

namespace util;

use Clickfwd\Yoyo\Yoyo;
use think\Request;
use think\Response;

class YoyoAdapter
{
    private static $yoyo = null;

    public static function init()
    {
        if (!self::$yoyo) {
            self::$yoyo = Yoyo::mount([
                'path' => APP_PATH . 'components',
                'namespace' => 'app\\components'
            ]);
        }
        return self::$yoyo;
    }

    public static function render($component, $props = [])
    {
        return self::init()->render($component, $props);
    }

    public static function handle(Request $request)
    {
        if ($request->header('HX-Request')) {
            return self::init()->resolve($request->param());
        }
        return null;
    }
}