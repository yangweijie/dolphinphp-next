<?php

namespace app\service;

/**
 * 表单组件服务类
 * Class FormComponentService
 * @package app\service
 */
class FormComponentService
{
    /**
     * 获取表单输入框配置
     * @param array $options 输入框选项
     * @return array
     */
    public function getInputGroupConfig($options = [])
    {
        $defaultOptions = [
            'type' => 'text',
            'size' => 'md',
            'disabled' => false,
            'readonly' => false,
            'required' => false,
            'hasError' => false,
            'hasSuccess' => false,
            'hasFeedback' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getInputClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取输入框的TailwindCSS类
     * @param array $options 输入框选项
     * @return string
     */
    protected function getInputClasses($options)
    {
        // 基础类
        $baseClasses = 'block w-full rounded-md border shadow-sm focus:ring focus:ring-opacity-50';
        
        // 尺寸类
        $sizeClasses = [
            'sm' => 'text-sm px-2 py-1',
            'md' => 'text-sm px-3 py-2',
            'lg' => 'text-base px-4 py-3'
        ];
        
        // 状态类
        if ($options['hasError']) {
            $stateClasses = 'border-red-300 focus:border-red-500 focus:ring-red-200';
        } elseif ($options['hasSuccess']) {
            $stateClasses = 'border-green-300 focus:border-green-500 focus:ring-green-200';
        } else {
            $stateClasses = 'border-gray-300 focus:border-blue-500 focus:ring-blue-200';
        }
        
        // 禁用状态类
        $disabledClasses = $options['disabled'] ? 'bg-gray-100 cursor-not-allowed' : '';
        
        // 只读状态类
        $readonlyClasses = $options['readonly'] ? 'bg-gray-50' : '';
        
        return implode(' ', [
            $baseClasses,
            $sizeClasses[$options['size']] ?? $sizeClasses['md'],
            $stateClasses,
            $disabledClasses,
            $readonlyClasses
        ]);
    }
    
    /**
     * 获取表单组配置
     * @param array $options 表单组选项
     * @return array
     */
    public function getFormGroupConfig($options = [])
    {
        $defaultOptions = [
            'hasError' => false,
            'hasSuccess' => false,
            'inline' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getFormGroupClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表单组的TailwindCSS类
     * @param array $options 表单组选项
     * @return string
     */
    protected function getFormGroupClasses($options)
    {
        // 基础类
        $baseClasses = 'mb-4';
        
        // 内联类
        $inlineClasses = $options['inline'] ? 'flex items-center' : '';
        
        // 状态类
        $stateClasses = '';
        if ($options['hasError']) {
            $stateClasses = 'text-red-600';
        } elseif ($options['hasSuccess']) {
            $stateClasses = 'text-green-600';
        }
        
        return implode(' ', [
            $baseClasses,
            $inlineClasses,
            $stateClasses
        ]);
    }
    
    /**
     * 获取验证错误提示配置
     * @param array $options 错误提示选项
     * @return array
     */
    public function getErrorConfig($options = [])
    {
        $defaultOptions = [
            'size' => 'sm'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getErrorClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取验证错误提示的TailwindCSS类
     * @param array $options 错误提示选项
     * @return string
     */
    protected function getErrorClasses($options)
    {
        // 基础类
        $baseClasses = 'mt-1 text-sm';
        
        // 尺寸类
        $sizeClasses = [
            'sm' => 'text-sm',
            'md' => 'text-base',
            'lg' => 'text-lg'
        ];
        
        // 错误状态类
        $errorClasses = 'text-red-600';
        
        return implode(' ', [
            $baseClasses,
            $sizeClasses[$options['size']] ?? $sizeClasses['sm'],
            $errorClasses
        ]);
    }
    
    /**
     * 获取表单提交按钮配置
     * @param array $options 按钮选项
     * @return array
     */
    public function getFormSubmitConfig($options = [])
    {
        $defaultOptions = [
            'style' => 'primary',
            'size' => 'md',
            'loading' => false,
            'block' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getFormSubmitClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表单提交按钮的TailwindCSS类
     * @param array $options 按钮选项
     * @return string
     */
    protected function getFormSubmitClasses($options)
    {
        // 基础类
        $baseClasses = 'inline-flex justify-center rounded-md border border-transparent py-2 px-4 text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2';
        
        // 样式类
        $styleClasses = [
            'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
            'secondary' => 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500',
            'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
            'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500'
        ];
        
        // 尺寸类
        $sizeClasses = [
            'sm' => 'text-xs px-2 py-1',
            'md' => 'text-sm px-4 py-2',
            'lg' => 'text-base px-6 py-3'
        ];
        
        // 加载状态类
        $loadingClasses = $options['loading'] ? 'opacity-75 cursor-not-allowed' : '';
        
        // 块级元素类
        $blockClasses = $options['block'] ? 'w-full' : '';
        
        return implode(' ', [
            $baseClasses,
            $styleClasses[$options['style']] ?? $styleClasses['primary'],
            $sizeClasses[$options['size']] ?? $sizeClasses['md'],
            $loadingClasses,
            $blockClasses
        ]);
    }
}