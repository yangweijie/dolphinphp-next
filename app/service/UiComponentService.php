<?php

namespace app\service;

/**
 * UI组件服务类
 * Class UiComponentService
 * @package app\service
 */
class UiComponentService
{
    /**
     * 获取按钮组件配置
     * @param array $options 按钮选项
     * @return array
     */
    public function getButtonConfig($options = [])
    {
        $defaultOptions = [
            'type' => 'button',
            'style' => 'primary',
            'size' => 'md',
            'disabled' => false,
            'block' => false,
            'icon' => '',
            'iconPosition' => 'left'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据样式和尺寸生成TailwindCSS类
        $classes = $this->getButtonClasses($options['style'], $options['size'], $options['disabled'], $options['block']);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取按钮的TailwindCSS类
     * @param string $style 按钮样式
     * @param string $size 按钮尺寸
     * @param bool $disabled 是否禁用
     * @param bool $block 是否块级元素
     * @return string
     */
    protected function getButtonClasses($style, $size, $disabled, $block)
    {
        // 基础类
        $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
        
        // 样式类
        $styleClasses = [
            'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500',
            'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
            'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
            'warning' => 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500',
            'info' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
            'light' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-500',
            'dark' => 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-500'
        ];
        
        // 尺寸类
        $sizeClasses = [
            'xs' => 'text-xs px-2 py-1',
            'sm' => 'text-sm px-3 py-1.5',
            'md' => 'text-sm px-4 py-2',
            'lg' => 'text-base px-6 py-3',
            'xl' => 'text-lg px-8 py-4'
        ];
        
        // 禁用状态类
        $disabledClasses = $disabled ? 'opacity-75 cursor-not-allowed' : '';
        
        // 块级元素类
        $blockClasses = $block ? 'w-full' : '';
        
        return implode(' ', [
            $baseClasses,
            $styleClasses[$style] ?? $styleClasses['primary'],
            $sizeClasses[$size] ?? $sizeClasses['md'],
            $disabledClasses,
            $blockClasses
        ]);
    }
    
    /**
     * 获取模态框组件配置
     * @param array $options 模态框选项
     * @return array
     */
    public function getModalConfig($options = [])
    {
        $defaultOptions = [
            'size' => 'md',
            'backdrop' => true,
            'keyboard' => true,
            'centered' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据尺寸生成TailwindCSS类
        $classes = $this->getModalClasses($options['size'], $options['centered']);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取模态框的TailwindCSS类
     * @param string $size 模态框尺寸
     * @param bool $centered 是否居中
     * @return string
     */
    protected function getModalClasses($size, $centered)
    {
        // 背景类
        $backdropClasses = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        
        // 模态框容器类
        $containerClasses = 'relative bg-white rounded-lg shadow-xl';
        
        // 尺寸类
        $sizeClasses = [
            'sm' => 'max-w-sm w-full mx-4',
            'md' => 'max-w-md w-full mx-4',
            'lg' => 'max-w-lg w-full mx-4',
            'xl' => 'max-w-xl w-full mx-4',
            '2xl' => 'max-w-2xl w-full mx-4',
            '3xl' => 'max-w-3xl w-full mx-4',
            '4xl' => 'max-w-4xl w-full mx-4',
            '5xl' => 'max-w-5xl w-full mx-4',
            '6xl' => 'max-w-6xl w-full mx-4',
            'full' => 'max-w-full w-full h-full'
        ];
        
        // 居中类
        $centeredClasses = $centered ? 'my-auto' : '';
        
        return [
            'backdrop' => $backdropClasses,
            'container' => implode(' ', [$containerClasses, $sizeClasses[$size] ?? $sizeClasses['md'], $centeredClasses])
        ];
    }
    
    /**
     * 获取警告组件配置
     * @param array $options 警告选项
     * @return array
     */
    public function getAlertConfig($options = [])
    {
        $defaultOptions = [
            'type' => 'info',
            'dismissible' => false,
            'icon' => true
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据类型生成TailwindCSS类
        $classes = $this->getAlertClasses($options['type']);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取警告的TailwindCSS类
     * @param string $type 警告类型
     * @return string
     */
    protected function getAlertClasses($type)
    {
        // 基础类
        $baseClasses = 'p-4 rounded-lg border';
        
        // 类型类
        $typeClasses = [
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'danger' => 'bg-red-50 border-red-200 text-red-800',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'info' => 'bg-blue-50 border-blue-200 text-blue-800'
        ];
        
        return implode(' ', [
            $baseClasses,
            $typeClasses[$type] ?? $typeClasses['info']
        ]);
    }
    
    /**
     * 获取加载组件配置
     * @param array $options 加载选项
     * @return array
     */
    public function getLoadingConfig($options = [])
    {
        $defaultOptions = [
            'type' => 'spinner',
            'size' => 'md',
            'fullScreen' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据类型和尺寸生成TailwindCSS类
        $classes = $this->getLoadingClasses($options['type'], $options['size'], $options['fullScreen']);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取加载的TailwindCSS类
     * @param string $type 加载类型
     * @param string $size 加载尺寸
     * @param bool $fullScreen 是否全屏
     * @return string
     */
    protected function getLoadingClasses($type, $size, $fullScreen)
    {
        // 尺寸类
        $sizeClasses = [
            'xs' => 'w-4 h-4',
            'sm' => 'w-6 h-6',
            'md' => 'w-8 h-8',
            'lg' => 'w-12 h-12',
            'xl' => 'w-16 h-16'
        ];
        
        // 全屏类
        $fullScreenClasses = $fullScreen ? 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50' : '';
        
        // 加载容器类
        $containerClasses = $fullScreen ? 'bg-white rounded-lg p-4' : '';
        
        return [
            'spinner' => implode(' ', [$sizeClasses[$size] ?? $sizeClasses['md'], 'animate-spin']),
            'fullScreen' => $fullScreenClasses,
            'container' => $containerClasses
        ];
    }
}