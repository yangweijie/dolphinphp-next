<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use app\services\cache\FileCache;

/**
 * 文件缓存驱动测试类
 */
class FileCacheTest extends TestCase
{
    /**
     * @var FileCache
     */
    protected FileCache $cache;

    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        $this->cache = new FileCache([
            'path' => runtime_path() . 'tests' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'ttl' => 3600
        ]);
    }

    /**
     * 测试基本缓存操作
     */
    public function testBasicOperations()
    {
        // 测试设置和获取
        $result = $this->cache->set('test_key', 'test_value');
        $this->assertTrue($result);

        $value = $this->cache->get('test_key');
        $this->assertEquals('test_value', $value);

        // 测试存在性检查
        $exists = $this->cache->has('test_key');
        $this->assertTrue($exists);

        // 测试删除
        $result = $this->cache->delete('test_key');
        $this->assertTrue($result);

        $exists = $this->cache->has('test_key');
        $this->assertFalse($exists);
    }

    /**
     * 测试缓存过期
     */
    public function testExpiration()
    {
        // 设置1秒过期的缓存
        $this->cache->set('expire_key', 'expire_value', 1);
        
        // 立即检查应该存在
        $this->assertTrue($this->cache->has('expire_key'));
        $this->assertEquals('expire_value', $this->cache->get('expire_key'));
        
        // 等待2秒
        sleep(2);
        
        // 检查应该不存在
        $this->assertFalse($this->cache->has('expire_key'));
        $this->assertNull($this->cache->get('expire_key'));
    }

    /**
     * 测试复杂数据类型
     */
    public function testComplexDataTypes()
    {
        // 测试数组
        $arrayData = ['name' => 'test', 'values' => [1, 2, 3]];
        $this->cache->set('array_key', $arrayData);
        $retrievedArray = $this->cache->get('array_key');
        $this->assertEquals($arrayData, $retrievedArray);

        // 测试对象
        $objectData = new \stdClass();
        $objectData->property = 'value';
        $this->cache->set('object_key', $objectData);
        $retrievedObject = $this->cache->get('object_key');
        $this->assertEquals($objectData, $retrievedObject);
    }

    /**
     * 测试驱动类型
     */
    public function testDriverType()
    {
        $driverType = $this->cache->getDriverType();
        $this->assertEquals('file', $driverType);
    }

    /**
     * 测试缓存清空
     */
    public function testClear()
    {
        // 设置多个缓存项
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        // 验证都存在
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        $this->assertTrue($this->cache->has('key3'));

        // 清空缓存
        $result = $this->cache->clear();
        $this->assertTrue($result);

        // 验证都不存在
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    /**
     * 测试默认值
     */
    public function testDefaultValues()
    {
        // 测试不存在的键返回默认值
        $value = $this->cache->get('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $value);

        // 测试不存在的键返回null
        $value = $this->cache->get('non_existent_key');
        $this->assertNull($value);
    }

    /**
     * 测试缓存目录创建
     */
    public function testCacheDirectoryCreation()
    {
        // 使用新的缓存目录
        $newCache = new FileCache([
            'path' => runtime_path() . 'tests' . DIRECTORY_SEPARATOR . 'new_cache' . DIRECTORY_SEPARATOR,
            'ttl' => 3600
        ]);

        // 应该能正常设置缓存
        $result = $newCache->set('test_key', 'test_value');
        $this->assertTrue($result);

        // 验证缓存文件存在
        $value = $newCache->get('test_key');
        $this->assertEquals('test_value', $value);

        // 清理
        $newCache->clear();
    }

    /**
     * 测试大容量数据
     */
    public function testLargeData()
    {
        // 创建大数组
        $largeData = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeData['key_' . $i] = 'value_' . $i;
        }

        // 应该能正常缓存
        $result = $this->cache->set('large_data', $largeData);
        $this->assertTrue($result);

        // 验证数据完整性
        $retrievedData = $this->cache->get('large_data');
        $this->assertEquals($largeData, $retrievedData);
        $this->assertCount(1000, $retrievedData);
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        // 清理所有测试缓存
        $this->cache->clear();
        
        // 清理测试目录
        $cachePath = runtime_path() . 'tests' . DIRECTORY_SEPARATOR;
        if (is_dir($cachePath)) {
            $this->removeDirectory($cachePath);
        }
    }

    /**
     * 递归删除目录
     *
     * @param string $dir
     */
    protected function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        $this->removeDirectory($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}