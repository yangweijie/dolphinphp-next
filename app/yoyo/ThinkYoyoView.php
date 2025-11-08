<?php

namespace app\yoyo;

use InvalidArgumentException;

class ThinkYoyoView
{
    protected array $locations = [];
    protected ?object $component = null;

    public function __construct()
    {
        // 默认位置：public/static/pages
        $this->locations[] = app()->getRootPath() . 'public/static/pages';
    }

    public function startYoyoRendering($component): void
    {
        $this->component = $component;
    }

    public function stopYoyoRendering(): void
    {
        $this->component = null;
    }

    public function render(string $name, array $vars = []): string
    {
        $file = $this->resolveFile($name);
        if (! $file) {
            throw new InvalidArgumentException("Yoyo view [{$name}] not found.");
        }
        $content = file_get_contents($file);
        // 简单变量替换（如果有 {{var}} 形式占位符）
        foreach ($vars as $k => $v) {
            $content = str_replace('{{' . $k . '}}', htmlspecialchars((string) $v, ENT_QUOTES), $content);
        }
        return $content;
    }

    public function makeFromString(string $content, array $vars = []): string
    {
        foreach ($vars as $k => $v) {
            $content = str_replace('{{' . $k . '}}', htmlspecialchars((string) $v, ENT_QUOTES), $content);
        }
        return $content;
    }

    public function exists(string $name): bool
    {
        return (bool) $this->resolveFile($name);
    }

    public function addNamespace($namespace, $hints)
    {
        // 简化：按位置追加目录
        foreach ((array) $hints as $path) {
            $this->addLocation($path);
        }
        return $this;
    }

    public function prependNamespace($namespace, $hints)
    {
        foreach ((array) $hints as $path) {
            $this->prependLocation($path);
        }
        return $this;
    }

    public function addLocation($location)
    {
        $this->locations[] = rtrim($location, DIRECTORY_SEPARATOR);
        return $this;
    }

    public function prependLocation($location)
    {
        array_unshift($this->locations, rtrim($location, DIRECTORY_SEPARATOR));
        return $this;
    }

    protected function resolveFile(string $name): ?string
    {
        // 处理组件视图名称 - 如果是组件类名，提取简化的视图名
        if (strpos($name, '\\') !== false) {
            // 如果是完整的类名如 app\yoyo\components\FormBuilder，提取 FormBuilder
            $parts = explode('\\', $name);
            $name = end($parts);
            // 移除 Builder 后缀
            $name = str_replace('Builder', '', $name);
            // 转换为小写
            $name = strtolower($name);
        }
        
        // 允许 name 带扩展或不带扩展
        foreach ($this->locations as $base) {
            $candidate = $base . DIRECTORY_SEPARATOR . $name;
            if (is_file($candidate)) {
                return $candidate;
            }
            $candidateHtml = $candidate . '.html';
            if (is_file($candidateHtml)) {
                return $candidateHtml;
            }
        }
        return null;
    }
}
