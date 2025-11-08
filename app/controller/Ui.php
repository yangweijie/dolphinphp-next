<?php
namespace app\controller;

use Clickfwd\Yoyo\Yoyo;
use Clickfwd\Yoyo\ViewProviders\YoyoViewProvider;
use app\yoyo\ThinkYoyoView;
use app\yoyo\components\FormBuilder;
use app\yoyo\components\TableBuilder;

class Ui
{
    private function initializeYoyo(): Yoyo
    {
        $yoyo = Yoyo::getInstance();
        $viewProvider = new YoyoViewProvider(new ThinkYoyoView());
        $yoyo->registerViewProviders(['default' => $viewProvider]);

        // 注册组件
        $yoyo->registerComponent('form', FormBuilder::class);
        $yoyo->registerComponent('table', TableBuilder::class);

        return $yoyo;
    }

    public function component(string $name)
    {
        $yoyo = $this->initializeYoyo();
        return $yoyo->mount($name)->render();
    }

    public function yoyo()
    {
        $yoyo = $this->initializeYoyo();
        return $yoyo->update();
    }
}
