<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use Thinkrix\Schema\Components\NaiveUI\{
    Flex,
    Card,
    Collapse,
    CollapseItem,
    Grid,
    GridItem,
    SwitchC,
    Text,
    Space,
    Button,
    Badge,
    Divider,
};
use Thinkrix\Schema\Components\Custom\Html;
use Thinkrix\Schema\Actions\{SetAction, CallAction};

/**
 * 页面配置 - 移动端首页模块管理
 *
 * 布局：左侧组件列表 | 中间手机预览 | 右侧模块配置
 * 交互：点击左侧图标 → 右侧显示对应配置面板
 */
class PageConfig extends BaseController
{
    /**
     * 组件模块定义
     */
    private array $modules = [
        ['key' => 'grid_area',    'label' => '金刚区',     'icon' => 'grid',     'color' => '#ff6b6b', 'bg' => '#fff1f0'],
        ['key' => 'notification', 'label' => '通知',       'icon' => 'chat',     'color' => '#4dabf7', 'bg' => '#e7f5ff'],
        ['key' => 'product_rec',  'label' => '商品推荐',   'icon' => 'shop',     'color' => '#9775fa', 'bg' => '#f3f0ff'],
        ['key' => 'carousel',     'label' => '轮播',       'icon' => 'slides',   'color' => '#69db7c', 'bg' => '#ebfbee'],
        ['key' => 'float_window', 'label' => '浮窗',       'icon' => 'popup',    'color' => '#ffa94d', 'bg' => '#fff4e6'],
        ['key' => 'custom',       'label' => '自定义',     'icon' => 'gear',     'color' => '#f783ac', 'bg' => '#fff0f6'],
        ['key' => 'popup',        'label' => '弹窗',       'icon' => 'window',   'color' => '#f783ac', 'bg' => '#fff0f6'],
        ['key' => 'top_nav',      'label' => '顶部导航栏', 'icon' => 'layout',   'color' => '#ffa94d', 'bg' => '#fff4e6'],
        ['key' => 'banner',       'label' => '瓷片区',     'icon' => 'image',    'color' => '#f783ac', 'bg' => '#fff0f6'],
        ['key' => 'search',       'label' => '搜索',       'icon' => 'search',   'color' => '#f783ac', 'bg' => '#fff0f6'],
        ['key' => 'flash_sale',   'label' => '秒杀',       'icon' => 'flash',    'color' => '#f783ac', 'bg' => '#fff0f6'],
        ['key' => 'activity',     'label' => '活动组件',   'icon' => 'event',    'color' => '#69db7c', 'bg' => '#ebfbee'],
        ['key' => 'top_nav_bar',  'label' => '顶部导航栏', 'icon' => 'grid2',    'color' => '#9775fa', 'bg' => '#f3f0ff'],
        ['key' => 'fission',      'label' => '裂变券',     'icon' => 'share',    'color' => '#f783ac', 'bg' => '#fff0f6'],
    ];

    /**
     * 页面 Schema
     */
    public function schema()
    {
        $schema = Flex::make()->props([
            'style' => [
                'height'     => '100%',
                'gap'        => '16px',
                'padding'    => '16px',
                'background' => '#f5f7fa',
            ],
        ])->data([
            'selectedModule' => '',
        ])->children([
            $this->buildLeftPanel(),
            $this->buildCenterPreview(),
            $this->buildRightPanel(),
        ]);

        return success($schema->toArray());
    }

    // ================================================================
    //  左侧面板
    // ================================================================

    private function buildLeftPanel(): Card
    {
        return Card::make()->props([
            'style' => [
                'width'         => '320px',
                'flexShrink'    => '0',
                'height'        => '100%',
                'overflow'      => 'hidden',
                'display'       => 'flex',
                'flexDirection' => 'column',
                'borderRadius'  => '12px',
            ],
            'contentStyle' => [
                'flex'      => '1',
                'overflowY' => 'auto',
                'padding'   => '0',
            ],
        ])->children([
            Text::make('首页模块设置')->props([
                'strong' => true,
                'style'  => ['padding' => '12px 16px 4px', 'fontSize' => '14px'],
            ]),
            ...$this->buildLeftSections(),
        ]);
    }

    private function buildLeftSections(): array
    {
        return [
            $this->buildSection('首页模块设置', [
                $this->buildIconItem('组件模块', '#e8f4ff', '#1890ff'),
            ]),
            $this->buildSection('组件模块配置', [
                $this->buildComponentGrid(),
            ], true),
            $this->buildSection('底部导航', [
                $this->buildIconItem('底部导航', '#fff7e6', '#fa8c16'),
            ]),
            $this->buildSection('登录设置', [
                $this->buildIconItem('登录设置', '#fff1f0', '#f5222d'),
            ]),
            $this->buildSection('基础信息', [
                $this->buildIconItem('基础信息', '#f6ffed', '#52c41a'),
            ]),
        ];
    }

    private function buildSection(string $title, array $children, bool $defaultExpanded = false): Collapse
    {
        return Collapse::make()->props([
            'defaultExpandedNames' => $defaultExpanded ? ['section'] : [],
            'style' => ['borderBottom' => '1px solid #f0f0f0'],
        ])->children([
            CollapseItem::make()->props([
                'title' => $title,
                'name'  => 'section',
            ])->children($children),
        ]);
    }

    private function buildIconItem(string $label, string $bg, string $color): Flex
    {
        return Flex::make()->props([
            'vertical' => true,
            'align'    => 'center',
            'style'    => [
                'cursor'       => 'pointer',
                'padding'      => '8px',
                'borderRadius' => '8px',
                'transition'   => 'background 0.2s',
            ],
        ])->children([
            Flex::make()->props([
                'style' => [
                    'width'           => '52px',
                    'height'          => '52px',
                    'borderRadius'    => '14px',
                    'background'      => $bg,
                    'alignItems'      => 'center',
                    'justifyContent'  => 'center',
                    'fontSize'        => '22px',
                    'color'           => $color,
                ],
            ])->children([
                Text::make('⬡'),
            ]),
            Text::make($label)->props([
                'style' => ['marginTop' => '6px', 'fontSize' => '12px', 'color' => '#333'],
            ]),
        ]);
    }

    private function buildComponentGrid(): Grid
    {
        $items = [];
        foreach ($this->modules as $mod) {
            $icon = Flex::make()->props([
                'style' => [
                    'width'          => '52px',
                    'height'         => '52px',
                    'borderRadius'   => '14px',
                    'background'     => $mod['bg'],
                    'alignItems'     => 'center',
                    'justifyContent' => 'center',
                    'fontSize'       => '22px',
                    'color'          => $mod['color'],
                    'transition'     => 'transform 0.2s',
                ],
            ])->children([
                Text::make('⬡'),
            ]);

            $label = Text::make($mod['label'])->props([
                'style' => ['marginTop' => '8px', 'fontSize' => '12px', 'color' => '#333', 'textAlign' => 'center'],
            ]);

            $card = Flex::make()->props([
                'vertical' => true,
                'align'    => 'center',
                'style'    => [
                    'cursor'       => 'pointer',
                    'padding'      => '8px 4px',
                    'borderRadius' => '10px',
                    'transition'   => 'all 0.2s',
                ],
            ])->on('click', SetAction::make('selectedModule', $mod['key']))
              ->children([$icon, $label]);

            $items[] = GridItem::make()->children([$card]);
        }

        return Grid::make()->props([
            'cols'  => 3,
            'xGap'  => 12,
            'yGap'  => 8,
        ])->children($items);
    }

    // ================================================================
    //  中间面板 - 手机预览
    // ================================================================

    private function buildCenterPreview(): Flex
    {
        return Flex::make()->props([
            'justify' => 'center',
            'align'   => 'flex-start',
            'style'   => [
                'flex'      => '1',
                'overflowY' => 'auto',
                'padding'   => '20px 0',
            ],
        ])->children([
            $this->buildSectionLabels(),
            $this->buildPhoneFrame(),
        ]);
    }

    private function buildSectionLabels(): Flex
    {
        $labels = ['金刚区', '通知区', '卡片区', '广告区', '折扣活动区', '商品区'];
        $children = [];
        foreach ($labels as $label) {
            $children[] = Text::make($label)->props([
                'style' => ['fontSize' => '12px', 'color' => '#999', 'padding' => '20px 0', 'whiteSpace' => 'nowrap'],
            ]);
        }

        return Flex::make()->props([
            'vertical' => true,
            'style'    => ['width' => '80px', 'flexShrink' => '0', 'paddingTop' => '40px'],
        ])->children($children);
    }

    private function buildPhoneFrame(): Html
    {
        $html = <<<'HTML'
<div style="width:375px;border-radius:36px;border:6px solid #1a1a2e;background:#fff;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.12);flex-shrink:0;">
  <div style="height:44px;background:#f8f8f8;display:flex;align-items:center;justify-content:space-between;padding:0 20px;font-size:13px;font-weight:600;color:#1a1a2e;">
    <span>9:41</span>
    <span style="display:flex;gap:6px;align-items:center;font-size:11px;">●●●●○ WiFi 🔋</span>
  </div>
  <div style="padding:0 0 20px;">
    <div style="padding:16px 12px 8px;">
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px 4px;">
        <div style="text-align:center;"><div style="width:40px;height:40px;border-radius:50%;background:#f0f5ff;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:14px;">话费</div><div style="font-size:10px;color:#666;margin-top:4px;">话费</div></div>
        <div style="text-align:center;"><div style="width:40px;height:40px;border-radius:50%;background:#fff7e6;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:14px;">美团</div><div style="font-size:10px;color:#666;margin-top:4px;">美团</div></div>
        <div style="text-align:center;"><div style="width:40px;height:40px;border-radius:50%;background:#fff1f0;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:14px;">加油</div><div style="font-size:10px;color:#666;margin-top:4px;">加油</div></div>
        <div style="text-align:center;"><div style="width:40px;height:40px;border-radius:50%;background:#f6ffed;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:14px;">叮咚</div><div style="font-size:10px;color:#666;margin-top:4px;">叮咚</div></div>
        <div style="text-align:center;"><div style="width:40px;height:40px;border-radius:50%;background:#f3f0ff;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:14px;">电影</div><div style="font-size:10px;color:#666;margin-top:4px;">电影</div></div>
      </div>
    </div>
    <div style="margin:8px 12px;padding:10px 14px;background:#f0f7ff;border-radius:8px;display:flex;align-items:center;gap:8px;">
      <span style="font-size:14px;">📢</span>
      <span style="font-size:13px;color:#333;"><span style="color:#999;">资讯</span> 商城上线啦！</span>
    </div>
    <div style="padding:8px 12px;">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
        <div style="text-align:center;"><div style="height:60px;background:#f5f7fa;border-radius:6px;"></div><div style="font-size:10px;color:#666;margin-top:4px;">京东电子卡</div></div>
        <div style="text-align:center;"><div style="height:60px;background:#f5f7fa;border-radius:6px;"></div><div style="font-size:10px;color:#666;margin-top:4px;">山姆电子卡</div></div>
        <div style="text-align:center;"><div style="height:60px;background:#f5f7fa;border-radius:6px;"></div><div style="font-size:10px;color:#666;margin-top:4px;">天猫电子卡</div></div>
        <div style="text-align:center;"><div style="height:60px;background:#f5f7fa;border-radius:6px;"></div><div style="font-size:10px;color:#666;margin-top:4px;">百果园电子卡</div></div>
      </div>
    </div>
    <div style="margin:8px 12px;height:80px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:500;">广告位</div>
    <div style="padding:12px 12px 4px;font-size:15px;font-weight:600;color:#1a1a2e;">猜你喜欢</div>
    <div style="padding:0 12px;">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;">
        <div style="background:#f5f7fa;border-radius:8px;overflow:hidden;"><div style="height:120px;background:#eee;"></div><div style="padding:8px;"><div style="font-size:12px;color:#333;">商品名称</div><div style="font-size:14px;color:#f5222d;font-weight:600;margin-top:4px;">¥99.00</div></div></div>
        <div style="background:#f5f7fa;border-radius:8px;overflow:hidden;"><div style="height:120px;background:#eee;"></div><div style="padding:8px;"><div style="font-size:12px;color:#333;">商品名称</div><div style="font-size:14px;color:#f5222d;font-weight:600;margin-top:4px;">¥199.00</div></div></div>
      </div>
    </div>
  </div>
</div>
HTML;

        return Html::make()->innerHTML($html);
    }

    // ================================================================
    //  右侧面板 - 模块配置
    // ================================================================

    private function buildRightPanel(): Card
    {
        // 先构建所有模块的开关行
        $switchItems = [];
        foreach ($this->modules as $mod) {
            $switchItems[] = $this->buildSwitchRow($mod['label'], $mod['key']);
        }

        // 构建每个模块的配置面板（点击左侧图标后显示）
        $configPanels = [];
        foreach ($this->modules as $mod) {
            $configPanels[] = $this->buildModuleConfig($mod['key'], $mod['label']);
        }

        return Card::make()->props([
            'style' => [
                'width'        => '380px',
                'flexShrink'   => '0',
                'height'       => '100%',
                'borderRadius' => '12px',
            ],
            'contentStyle' => [
                'overflowY' => 'auto',
            ],
        ])->children([
            // 标题区域
            Flex::make()->props([
                'justify' => 'space-between',
                'align'   => 'center',
                'style'   => ['marginBottom' => '12px'],
            ])->children([
                Text::make('组件模块 / 配置信息')->props([
                    'strong' => true,
                    'style'  => ['fontSize' => '16px'],
                ]),
            ]),

            // 模块开关列表
            Space::make()->props([
                'vertical' => true,
                'size'     => 0,
            ])->children($switchItems),

            // 分割线
            Divider::make(),

            // 模块配置面板（条件渲染）
            ...$configPanels,
        ]);
    }

    private function buildSwitchRow(string $label, string $key): Flex
    {
        return Flex::make()->props([
            'justify' => 'space-between',
            'align'   => 'center',
            'style'   => [
                'padding'      => '14px 0',
                'borderBottom' => '1px solid #f5f5f5',
            ],
        ])->children([
            Text::make('模块：' . $label)->props([
                'style' => ['fontSize' => '14px', 'color' => '#333'],
            ]),
            Flex::make()->props([
                'align' => 'center',
                'gap'   => 10,
            ])->children([
                SwitchC::make()->model('modules.' . $key),
                Text::make('开启中')->props([
                    'style' => ['fontSize' => '12px', 'color' => '#999'],
                ]),
            ]),
        ]);
    }

    /**
     * 模块配置面板（条件渲染 - 选中时显示）
     */
    private function buildModuleConfig(string $key, string $label): Flex
    {
        return Flex::make()->props([
            'vertical' => true,
            'style'    => [
                'padding'     => '16px',
                'margin'      => '8px 0',
                'background'  => '#fafafa',
                'borderRadius' => '8px',
                'border'      => '1px solid #e8e8e8',
            ],
        ])->if('selectedModule === "' . $key . '"')
          ->children([
              Text::make($label . ' 配置')->props([
                  'strong' => true,
                  'style'  => ['fontSize' => '14px', 'marginBottom' => '12px'],
              ]),
              Text::make('此处显示 ' . $label . ' 的详细配置项...')->props([
                  'style' => ['fontSize' => '13px', 'color' => '#666'],
              ]),
          ]);
    }
}
