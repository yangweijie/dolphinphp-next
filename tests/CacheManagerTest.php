<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use app\services\cache\CacheManager;
use app\services\cache\FileCache;

/**
 * 缓存管理器测试类
 */
class CacheManagerTest extends TestCase
{
    /**
     * 测试缓存基本操作
     */
    public function testBasicCacheOperations()
    {
        // 测试设置和获取
        $result = CacheManager::set('test_key', 'test_value', 3600);
        $this->assertTrue($result);

        $value = CacheManager::get('test_key');
        $this->assertEquals('test_value', $value);

        // 测试存在性检查
        $exists = CacheManager::has('test_key');
        $this->assertTrue($exists);

        // 测试删除
        $result = CacheManager::delete('test_key');
        $this->assertTrue($result);

        $exists = CacheManager::has('test_key');
        $this->assertFalse($exists);
    }

    /**
     * 测试缓存过期
     */
    public function testCacheExpiration()
    {
        // 设置1秒过期的缓存
        CacheManager::set('expire_key', 'expire_value', 1);
        
        // 立即检查应该存在
        $this->assertTrue(CacheManager::has('expire_key'));
        $this->assertEquals('expire_value', CacheManager::get('expire_key'));
        
        // 等待2秒
        sleep(2);
        
        // 检查应该不存在
        $this->assertFalse(CacheManager::has('expire_key'));
        $this->assertNull(CacheManager::get('expire_key'));
    }

    /**
     * 测试复杂数据类型缓存
     */
    public function testComplexDataTypes()
    {
        // 测试数组
        $arrayData = ['name' => 'test', 'values' => [1, 2, 3]];
        CacheManager::set('array_key', $arrayData);
        $retrievedArray = CacheManager::get('array_key');
        $this->assertEquals($arrayData, $retrievedArray);

        // 测试对象
        $objectData = new \stdClass();
        $objectData->property = 'value';
        CacheManager::set('object_key', $objectData);
        $retrievedObject = CacheManager::get('object_key');
        $this->assertEquals($objectData, $retrievedObject);

        // 清理
        CacheManager::delete('array_key');
        CacheManager::delete('object_key');
    }

    /**
     * 测试缓存键生成
     */
    public function testCacheKeyGeneration()
    {
        $formKey = CacheManager::getFormConfigKey('test_form');
        $this->assertEquals('form_config:test_form', $formKey);

        $tableKey = CacheManager::getTableConfigKey('test_table');
        $this->assertEquals('table_config:test_table', $tableKey);

        $permissionKey = CacheManager::getPermissionConfigKey('test_permission');
        $this->assertEquals('permission_config:test_permission', $permissionKey);
    }

    /**
     * 测试缓存清空
     */
    public function testCacheClear()
    {
        // 设置多个缓存项
        CacheManager::set('key1', 'value1');
        CacheManager::set('key2', 'value2');
        CacheManager::set('key3', 'value3');

        // 验证都存在
        $this->assertTrue(CacheManager::has('key1'));
        $this->assertTrue(CacheManager::has('key2'));
        $this->assertTrue(CacheManager::has('key3'));

        // 清空缓存
        $result = CacheManager::clear();
        $this->assertTrue($result);

        // 验证都不存在
        $this->assertFalse(CacheManager::has('key1'));
        $this->assertFalse(CacheManager::has('key2'));
        $this->assertFalse(CacheManager::has('key3'));
    }

    /**
     * 测试默认值
     */
    public function testDefaultValues()
    {
        // 测试不存在的键返回默认值
        $value = CacheManager::get('non_existent_key', 'default_value');
        $this->assertEquals('default_value', $value);

        // 测试不存在的键返回null
        $value = CacheManager::get('non_existent_key');
        $this->assertNull($value);
    }

    /**
     * 测试错误处理
     */
    public function testErrorHandling()
    {
        // 即使出现错误也应该返回默认值而不是抛出异常
        $value = CacheManager::get('error_key', 'error_default');
        $this->assertEquals('error_default', $value);

        // 设置操作也应该优雅处理错误
        $result = CacheManager::set('error_key', 'error_value');
        $this->assertIsBool($result);
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        // 清理所有测试缓存
        CacheManager::clear();
    }
}