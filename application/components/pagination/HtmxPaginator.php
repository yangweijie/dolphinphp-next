<?php
namespace app\components\pagination;

use Clickfwd\Yoyo\Component;

class HtmxPaginator extends Component
{
    public $current_page = 1;
    public $per_page = 15;
    public $total = 0;
    public $total_pages = 0;
    public $show_pages = 7; // 显示的页码数量
    public $has_prev = false;
    public $has_next = false;
    public $prev_page = 0;
    public $next_page = 0;
    public $start_page = 1;
    public $end_page = 1;
    public $start_record = 0;
    public $end_record = 0;

    // HTMX相关配置
    public $target = '#content'; // HTMX目标容器
    public $url = ''; // 基础URL
    public $params = []; // 额外参数
    public $indicator = '#loading'; // 加载指示器
    public $push_url = true; // 是否推送URL到历史记录
    public $replace_url = false; // 是否替换当前URL

    // 显示选项
    public $show_info = true; // 显示信息文本
    public $show_jumper = true; // 显示跳转输入框
    public $show_per_page = true; // 显示每页条数选择
    public $per_page_options = [10, 15, 20, 30, 50, 100];
    public $show_total = true; // 显示总数
    public $show_range = true; // 显示范围

    // 样式配置
    public $size = 'normal'; // small, normal, large
    public $theme = 'default'; // default, simple, minimal
    public $position = 'center'; // left, center, right

    public function mount($config = [])
    {
        $this->current_page = $config['current_page'] ?? 1;
        $this->per_page = $config['per_page'] ?? 15;
        $this->total = $config['total'] ?? 0;
        $this->url = $config['url'] ?? request()->url();
        $this->params = $config['params'] ?? [];
        $this->target = $config['target'] ?? '#content';
        $this->indicator = $config['indicator'] ?? '#loading';
        $this->push_url = $config['push_url'] ?? true;
        $this->replace_url = $config['replace_url'] ?? false;

        // 显示选项
        $this->show_info = $config['show_info'] ?? true;
        $this->show_jumper = $config['show_jumper'] ?? true;
        $this->show_per_page = $config['show_per_page'] ?? true;
        $this->per_page_options = $config['per_page_options'] ?? [10, 15, 20, 30, 50, 100];
        $this->show_total = $config['show_total'] ?? true;
        $this->show_range = $config['show_range'] ?? true;

        // 样式配置
        $this->size = $config['size'] ?? 'normal';
        $this->theme = $config['theme'] ?? 'default';
        $this->position = $config['position'] ?? 'center';
        $this->show_pages = $config['show_pages'] ?? 7;

        $this->calculatePages();
    }

    public function goToPage($page)
    {
        $page = max(1, min($this->total_pages, intval($page)));
        $this->current_page = $page;
        $this->calculatePages();

        $this->emit('page-changed', [
            'page' => $this->current_page,
            'per_page' => $this->per_page,
            'params' => $this->params
        ]);
    }

    public function changePerPage($per_page)
    {
        $this->per_page = intval($per_page);
        $this->current_page = 1; // 重置到第一页
        $this->calculatePages();

        $this->emit('per-page-changed', [
            'page' => $this->current_page,
            'per_page' => $this->per_page,
            'params' => $this->params
        ]);
    }

    public function updated($property, $value)
    {
        if ($property === 'current_page') {
            $this->goToPage($value);
        } elseif ($property === 'per_page') {
            $this->changePerPage($value);
        }
    }

    private function calculatePages()
    {
        $this->total_pages = $this->per_page > 0 ? ceil($this->total / $this->per_page) : 0;
        $this->has_prev = $this->current_page > 1;
        $this->has_next = $this->current_page < $this->total_pages;
        $this->prev_page = max(1, $this->current_page - 1);
        $this->next_page = min($this->total_pages, $this->current_page + 1);

        // 计算显示的页码范围
        $half_show = floor($this->show_pages / 2);
        $this->start_page = max(1, $this->current_page - $half_show);
        $this->end_page = min($this->total_pages, $this->start_page + $this->show_pages - 1);

        // 调整开始页码，确保显示足够的页码
        if ($this->end_page - $this->start_page + 1 < $this->show_pages) {
            $this->start_page = max(1, $this->end_page - $this->show_pages + 1);
        }

        // 计算记录范围
        $this->start_record = ($this->current_page - 1) * $this->per_page + 1;
        $this->end_record = min($this->current_page * $this->per_page, $this->total);
    }

    private function buildUrl($page, $per_page = null)
    {
        $params = array_merge($this->params, [
            'page' => $page,
            'per_page' => $per_page ?: $this->per_page
        ]);

        return $this->url . '?' . http_build_query($params);
    }

    private function getHtmxAttributes($page, $per_page = null)
    {
        $url = $this->buildUrl($page, $per_page);
        $attrs = [
            'hx-get' => $url,
            'hx-target' => $this->target,
            'hx-indicator' => $this->indicator
        ];

        if ($this->push_url) {
            $attrs['hx-push-url'] = 'true';
        } elseif ($this->replace_url) {
            $attrs['hx-replace-url'] = 'true';
        }

        return implode(' ', array_map(function($key, $value) {
            return $key . '="' . htmlspecialchars($value) . '"';
        }, array_keys($attrs), $attrs));
    }

    public function render()
    {
        if ($this->total_pages <= 1 && $this->theme !== 'minimal') {
            return '';
        }

        $size_class = "pagination-{$this->size}";
        $theme_class = "pagination-theme-{$this->theme}";
        $position_class = "pagination-{$this->position}";

        return <<<HTML
<div class="htmx-pagination {$size_class} {$theme_class} {$position_class}">
    <!-- 分页信息 -->
    @if($this->show_info && $this->total > 0)
        <div class="pagination-info">
            @if($this->show_range)
                显示 <strong>{$this->start_record}</strong> 到 <strong>{$this->end_record}</strong> 条
            @endif
            @if($this->show_total)
                ，共 <strong>{$this->total}</strong> 条记录
            @endif
            @if($this->total_pages > 1)
                ，第 <strong>{$this->current_page}</strong> 页 / 共 <strong>{$this->total_pages}</strong> 页
            @endif
        </div>
    @endif
    
    <!-- 主分页导航 -->
    @if($this->total_pages > 1)
        <div class="pagination-nav">
            <nav aria-label="分页导航">
                <ul class="pagination">
                    <!-- 首页 -->
                    @if($this->current_page > 1)
                        <li class="page-item">
                            <a class="page-link" 
                               {$this->getHtmxAttributes(1)}
                               title="首页">
                                <i class="fa fa-angle-double-left"></i>
                            </a>
                        </li>
                    @endif
                    
                    <!-- 上一页 -->
                    @if($this->has_prev)
                        <li class="page-item">
                            <a class="page-link" 
                               {$this->getHtmxAttributes($this->prev_page)}
                               title="上一页">
                                <i class="fa fa-angle-left"></i>
                            </a>
                        </li>
                    @endif
                    
                    <!-- 页码 -->
                    @for($i = $this->start_page; $i <= $this->end_page; $i++)
                        @if($i == $this->current_page)
                            <li class="page-item active">
                                <span class="page-link">{$i}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" 
                                   {$this->getHtmxAttributes($i)}>
                                    {$i}
                                </a>
                            </li>
                        @endif
                    @endfor
                    
                    <!-- 省略号和最后页 -->
                    @if($this->end_page < $this->total_pages)
                        @if($this->end_page < $this->total_pages - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" 
                               {$this->getHtmxAttributes($this->total_pages)}>
                                {$this->total_pages}
                            </a>
                        </li>
                    @endif
                    
                    <!-- 下一页 -->
                    @if($this->has_next)
                        <li class="page-item">
                            <a class="page-link" 
                               {$this->getHtmxAttributes($this->next_page)}
                               title="下一页">
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    @endif
                    
                    <!-- 尾页 -->
                    @if($this->current_page < $this->total_pages)
                        <li class="page-item">
                            <a class="page-link" 
                               {$this->getHtmxAttributes($this->total_pages)}
                               title="尾页">
                                <i class="fa fa-angle-double-right"></i>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif
    
    <!-- 分页工具 -->
    <div class="pagination-tools">
        <!-- 每页条数选择 -->
        @if($this->show_per_page && count($this->per_page_options) > 1)
            <div class="per-page-selector">
                <label>每页显示</label>
                <select class="form-control input-sm" 
                        wire:model="per_page">
                    @foreach($this->per_page_options as $option)
                        <option value="{$option}" 
                                @if($this->per_page == $option) selected @endif>
                            {$option}
                        </option>
                    @endforeach
                </select>
                <label>条</label>
            </div>
        @endif
        
        <!-- 页码跳转 -->
        @if($this->show_jumper && $this->total_pages > 1)
            <div class="page-jumper">
                <label>跳转到</label>
                <input type="number" 
                       class="form-control input-sm" 
                       min="1" 
                       max="{$this->total_pages}"
                       value="{$this->current_page}"
                       wire:model.lazy="current_page"
                       style="width: 60px;">
                <label>页</label>
            </div>
        @endif
    </div>
</div>
 
<!-- 加载指示器 -->
<div id="loading" class="htmx-indicator">
    <div class="loading-content">
        <i class="fa fa-spinner fa-spin"></i>
        <span>加载中...</span>
    </div>
</div>
 
<style>
.htmx-pagination {
    margin: 20px 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px;
}
 
.pagination-center {
    justify-content: center;
}
 
.pagination-left {
    justify-content: flex-start;
}
 
.pagination-right {
    justify-content: flex-end;
}
 
.pagination-info {
    color: #666;
    font-size: 14px;
}
 
.pagination-nav {
    flex: 1;
    display: flex;
    justify-content: center;
}
 
.pagination {
    margin: 0;
    display: flex;
    list-style: none;
    padding: 0;
}
 
.page-item {
    margin: 0 2px;
}
 
.page-link {
    display: block;
    padding: 8px 12px;
    text-decoration: none;
    border: 1px solid #ddd;
    color: #337ab7;
    background: #fff;
    transition: all 0.2s;
    cursor: pointer;
}
 
.page-link:hover {
    background: #f5f5f5;
    border-color: #337ab7;
    color: #337ab7;
    text-decoration: none;
}
 
.page-item.active .page-link {
    background: #337ab7;
    border-color: #337ab7;
    color: #fff;
}
 
.page-item.disabled .page-link {
    color: #999;
    cursor: not-allowed;
    background: #f5f5f5;
}
 
.pagination-tools {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 14px;
}
 
.per-page-selector,
.page-jumper {
    display: flex;
    align-items: center;
    gap: 5px;
}
 
.per-page-selector select,
.page-jumper input {
    width: auto;
    min-width: 60px;
}
 
/* 小尺寸 */
.pagination-small .page-link {
    padding: 4px 8px;
    font-size: 12px;
}
 
.pagination-small .pagination-info {
    font-size: 12px;
}
 
/* 大尺寸 */
.pagination-large .page-link {
    padding: 12px 16px;
    font-size: 16px;
}
 
.pagination-large .pagination-info {
    font-size: 16px;
}
 
/* 简单主题 */
.pagination-theme-simple .page-link {
    border: none;
    border-radius: 4px;
}
 
.pagination-theme-simple .page-item.active .page-link {
    background: #337ab7;
}
 
/* 最小主题 */
.pagination-theme-minimal .pagination-info {
    display: none;
}
 
.pagination-theme-minimal .pagination-tools {
    display: none;
}
 
/* HTMX加载指示器 */
.htmx-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    z-index: 9999;
    display: none;
}
 
.htmx-indicator.htmx-request {
    display: block;
}
 
.loading-content {
    display: flex;
    align-items: center;
    gap: 10px;
}
 
/* 移动端响应式 */
@media (max-width: 768px) {
    .htmx-pagination {
        flex-direction: column;
        gap: 10px;
    }
    
    .pagination-nav {
        order: 1;
    }
    
    .pagination-info {
        order: 2;
        text-align: center;
    }
    
    .pagination-tools {
        order: 3;
        flex-direction: column;
        gap: 10px;
    }
    
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .page-link {
        padding: 6px 10px;
        font-size: 12px;
    }
}
 
@media (max-width: 480px) {
    .pagination {
        gap: 2px;
    }
    
    .page-link {
        padding: 4px 8px;
        font-size: 11px;
    }
    
    /* 在小屏幕上隐藏部分页码 */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}
</style>
HTML;
    }
}