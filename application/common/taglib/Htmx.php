<?php

namespace app\common\taglib;

use think\template\TagLib;

class Htmx extends TagLib
{
    protected $tags = [
        'paginate' => ['attr' => 'config', 'close' => 0]
    ];

    /**
     * 分页标签
     */
    public function tagPaginate($tag, $content)
    {
        $config = isset($tag['config']) ? $tag['config'] : '[]';
        return '<?php echo htmx_paginate(' . $config . '); ?>';
    }
}