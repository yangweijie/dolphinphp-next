<?php

namespace app\service;

/**
 * 表格组件服务类
 * Class TableComponentService
 * @package app\service
 */
class TableComponentService
{
    /**
     * 获取表格容器配置
     * @param array $options 表格选项
     * @return array
     */
    public function getTableContainerConfig($options = [])
    {
        $defaultOptions = [
            'responsive' => true,
            'striped' => false,
            'hover' => false,
            'bordered' => true,
            'size' => 'md'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getTableContainerClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表格容器的TailwindCSS类
     * @param array $options 表格选项
     * @return string
     */
    protected function getTableContainerClasses($options)
    {
        // 响应式类
        $responsiveClasses = $options['responsive'] ? 'overflow-x-auto' : '';
        
        return $responsiveClasses;
    }
    
    /**
     * 获取表格配置
     * @param array $options 表格选项
     * @return array
     */
    public function getTableConfig($options = [])
    {
        $defaultOptions = [
            'striped' => false,
            'hover' => false,
            'bordered' => true,
            'size' => 'md'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getTableClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表格的TailwindCSS类
     * @param array $options 表格选项
     * @return string
     */
    protected function getTableClasses($options)
    {
        // 基础类
        $baseClasses = 'min-w-full divide-y divide-gray-200';
        
        // 边框类
        $borderedClasses = $options['bordered'] ? 'border border-gray-200' : '';
        
        return implode(' ', [
            $baseClasses,
            $borderedClasses
        ]);
    }
    
    /**
     * 获取表头配置
     * @param array $options 表头选项
     * @return array
     */
    public function getTheadConfig($options = [])
    {
        $defaultOptions = [
            'sticky' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getTheadClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表头的TailwindCSS类
     * @param array $options 表头选项
     * @return string
     */
    protected function getTheadClasses($options)
    {
        // 基础类
        $baseClasses = 'bg-gray-50';
        
        // 粘性类
        $stickyClasses = $options['sticky'] ? 'sticky top-0 z-10' : '';
        
        return implode(' ', [
            $baseClasses,
            $stickyClasses
        ]);
    }
    
    /**
     * 获取表头单元格配置
     * @param array $options 单元格选项
     * @return array
     */
    public function getThConfig($options = [])
    {
        $defaultOptions = [
            'align' => 'left',
            'sortable' => false,
            'size' => 'md'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getThClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表头单元格的TailwindCSS类
     * @param array $options 单元格选项
     * @return string
     */
    protected function getThClasses($options)
    {
        // 基础类
        $baseClasses = 'px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider';
        
        // 对齐类
        $alignClasses = [
            'left' => 'text-left',
            'center' => 'text-center',
            'right' => 'text-right'
        ];
        
        // 尺寸类
        $sizeClasses = [
            'sm' => 'px-3 py-2 text-xs',
            'md' => 'px-6 py-3 text-xs',
            'lg' => 'px-8 py-4 text-sm'
        ];
        
        return implode(' ', [
            $baseClasses,
            $alignClasses[$options['align']] ?? $alignClasses['left'],
            $sizeClasses[$options['size']] ?? $sizeClasses['md']
        ]);
    }
    
    /**
     * 获取表格主体配置
     * @param array $options 主体选项
     * @return array
     */
    public function getTbodyConfig($options = [])
    {
        $defaultOptions = [
            'striped' => false
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getTbodyClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表格主体的TailwindCSS类
     * @param array $options 主体选项
     * @return string
     */
    protected function getTbodyClasses($options)
    {
        // 基础类
        $baseClasses = 'bg-white divide-y divide-gray-200';
        
        return $baseClasses;
    }
    
    /**
     * 获取表格行配置
     * @param array $options 行选项
     * @return array
     */
    public function getTrConfig($options = [])
    {
        $defaultOptions = [
            'striped' => false,
            'hover' => false,
            'index' => 0
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getTrClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表格行的TailwindCSS类
     * @param array $options 行选项
     * @return string
     */
    protected function getTrClasses($options)
    {
        // 基础类
        $baseClasses = '';
        
        // 斑马纹类
        $stripedClasses = ($options['striped'] && $options['index'] % 2 === 0) ? 'bg-gray-50' : '';
        
        // 悬停类
        $hoverClasses = $options['hover'] ? 'hover:bg-gray-100' : '';
        
        return implode(' ', [
            $baseClasses,
            $stripedClasses,
            $hoverClasses
        ]);
    }
    
    /**
     * 获取表格单元格配置
     * @param array $options 单元格选项
     * @return array
     */
    public function getTdConfig($options = [])
    {
        $defaultOptions = [
            'align' => 'left',
            'size' => 'md'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getTdClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取表格单元格的TailwindCSS类
     * @param array $options 单元格选项
     * @return string
     */
    protected function getTdClasses($options)
    {
        // 基础类
        $baseClasses = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
        
        // 对齐类
        $alignClasses = [
            'left' => 'text-left',
            'center' => 'text-center',
            'right' => 'text-right'
        ];
        
        // 尺寸类
        $sizeClasses = [
            'sm' => 'px-3 py-2 text-xs',
            'md' => 'px-6 py-4 text-sm',
            'lg' => 'px-8 py-5 text-base'
        ];
        
        return implode(' ', [
            $baseClasses,
            $alignClasses[$options['align']] ?? $alignClasses['left'],
            $sizeClasses[$options['size']] ?? $sizeClasses['md']
        ]);
    }
    
    /**
     * 获取分页配置
     * @param array $options 分页选项
     * @return array
     */
    public function getPaginationConfig($options = [])
    {
        $defaultOptions = [
            'align' => 'right',
            'size' => 'md'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // 根据选项生成TailwindCSS类
        $classes = $this->getPaginationClasses($options);
        
        return [
            'classes' => $classes,
            'options' => $options
        ];
    }
    
    /**
     * 获取分页的TailwindCSS类
     * @param array $options 分页选项
     * @return string
     */
    protected function getPaginationClasses($options)
    {
        // 基础类
        $baseClasses = 'flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6';
        
        // 对齐类
        $alignClasses = [
            'left' => 'justify-start',
            'center' => 'justify-center',
            'right' => 'justify-end'
        ];
        
        return implode(' ', [
            $baseClasses,
            $alignClasses[$options['align']] ?? $alignClasses['right']
        ]);
    }
}