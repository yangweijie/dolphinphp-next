<?php

namespace app\service;

use app\model\Form;
use think\facade\Db;

/**
 * 表单服务类
 * Class FormService
 * @package app\service
 */
class FormService extends BaseService
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Form();
    }

    /**
     * 获取表单列表（带搜索和分页）
     * @param array $params 查询参数
     * @return array
     */
    public function getFormList($params = [])
    {
        $where = $this->buildWhere($params);
        $options = [
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 15,
            'sort' => $this->buildSort($params),
            'with' => ['creator', 'updater']
        ];

        // 搜索条件
        if (!empty($params['search'])) {
            $options['search'] = [
                'title' => $params['search'],
                'name' => $params['search']
            ];
        }

        return $this->getList($where, $options);
    }

    /**
     * 获取表单信息
     * @param int $id 表单ID
     * @return array|null
     */
    public function getFormInfo($id)
    {
        return $this->getInfo($id, ['creator', 'updater']);
    }

    /**
     * 根据名称获取表单信息
     * @param string $name 表单名称
     * @return array|null
     */
    public function getFormByName($name)
    {
        try {
            $form = $this->model->where('name', $name)->where('status', Form::STATUS_ENABLE)->find();
            return $form ? $form->toArray() : null;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    /**
     * 创建表单
     * @param array $data 表单数据
     * @return bool|int
     */
    public function createForm($data)
    {
        try {
            // 检查名称是否已存在
            if ($this->model->where('name', $data['name'])->find()) {
                $this->setError('表单名称已存在');
                return false;
            }

            // 验证字段配置
            if (!empty($data['fields'])) {
                $validateResult = $this->validateFields($data['fields']);
                if ($validateResult !== true) {
                    $this->setError($validateResult);
                    return false;
                }
            }

            // 设置默认状态
            $data['status'] = $data['status'] ?? Form::STATUS_ENABLE;

            return $this->add($data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 更新表单
     * @param int $id 表单ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateForm($id, $data)
    {
        try {
            $form = $this->model->find($id);
            if (!$form) {
                $this->setError('表单不存在');
                return false;
            }

            // 如果修改了名称，检查新名称是否已存在
            if (isset($data['name']) && $data['name'] != $form->name) {
                if ($this->model->where('name', $data['name'])->where('id', '<>', $id)->find()) {
                    $this->setError('表单名称已存在');
                    return false;
                }
            }

            // 验证字段配置
            if (!empty($data['fields'])) {
                $validateResult = $this->validateFields($data['fields']);
                if ($validateResult !== true) {
                    $this->setError($validateResult);
                    return false;
                }
            }

            return $this->edit($id, $data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 删除表单
     * @param int $id 表单ID
     * @return bool
     */
    public function deleteForm($id)
    {
        return $this->del($id);
    }

    /**
     * 批量删除表单
     * @param array $ids 表单ID数组
     * @return bool
     */
    public function batchDeleteForm($ids)
    {
        return $this->batchDel($ids);
    }

    /**
     * 修改表单状态
     * @param int $id 表单ID
     * @param int $status 状态值
     * @return bool
     */
    public function modifyFormStatus($id, $status)
    {
        return $this->modifyStatus($id, $status);
    }

    /**
     * 验证字段配置
     * @param array $fields 字段配置
     * @return true|string
     */
    protected function validateFields($fields)
    {
        if (!is_array($fields)) {
            return '字段配置必须是数组';
        }

        if (empty($fields)) {
            return '字段配置不能为空';
        }

        $fieldNames = [];
        foreach ($fields as $index => $field) {
            // 检查必填字段
            if (empty($field['name'])) {
                return '第' . ($index + 1) . '个字段缺少name属性';
            }
            if (empty($field['type'])) {
                return '第' . ($index + 1) . '个字段缺少type属性';
            }
            if (empty($field['title'])) {
                return '第' . ($index + 1) . '个字段缺少title属性';
            }

            // 检查字段名是否重复
            if (in_array($field['name'], $fieldNames)) {
                return '字段名 ' . $field['name'] . ' 重复';
            }
            $fieldNames[] = $field['name'];

            // 验证字段类型
            $validTypes = ['text', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'datetime', 'file', 'image', 'password', 'email', 'url', 'color', 'switch'];
            if (!in_array($field['type'], $validTypes)) {
                return '第' . ($index + 1) . '个字段的type属性不合法';
            }

            // 验证选项字段
            if (in_array($field['type'], ['select', 'radio', 'checkbox'])) {
                if (empty($field['options']) || !is_array($field['options'])) {
                    return '第' . ($index + 1) . '个字段缺少options配置';
                }
            }
        }

        return true;
    }

    /**
     * 获取表单字段类型列表
     * @return array
     */
    public function getFieldTypes()
    {
        return [
            ['value' => 'text', 'label' => '文本框'],
            ['value' => 'textarea', 'label' => '多行文本'],
            ['value' => 'select', 'label' => '下拉选择'],
            ['value' => 'radio', 'label' => '单选框'],
            ['value' => 'checkbox', 'label' => '复选框'],
            ['value' => 'number', 'label' => '数字'],
            ['value' => 'date', 'label' => '日期'],
            ['value' => 'datetime', 'label' => '日期时间'],
            ['value' => 'file', 'label' => '文件上传'],
            ['value' => 'image', 'label' => '图片上传'],
            ['value' => 'password', 'label' => '密码框'],
            ['value' => 'email', 'label' => '邮箱'],
            ['value' => 'url', 'label' => '网址'],
            ['value' => 'color', 'label' => '颜色选择'],
            ['value' => 'switch', 'label' => '开关'],
            ['value' => 'editor', 'label' => '富文本编辑器'],
        ];
    }

    /**
     * 获取表单统计信息
     * @return array
     */
    public function getStatistics()
    {
        try {
            $total = $this->model->count();
            $enable = $this->model->where('status', Form::STATUS_ENABLE)->count();
            $disable = $this->model->where('status', Form::STATUS_DISABLE)->count();

            return [
                'total' => $total,
                'enable' => $enable,
                'disable' => $disable
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'enable' => 0,
                'disable' => 0
            ];
        }
    }

    /**
     * 表单提交处理
     * @param int $id 表单ID
     * @param array $data 提交数据
     * @return array|bool
     */
    public function submitForm($id, $data)
    {
        try {
            // 获取表单信息
            $form = $this->model->find($id);
            if (!$form) {
                $this->setError('表单不存在');
                return false;
            }

            // 检查表单状态
            if ($form->status != Form::STATUS_ENABLE) {
                $this->setError('表单已禁用，无法提交');
                return false;
            }

            // 验证提交的数据
            $validateResult = $this->validateSubmitData($form, $data);
            if ($validateResult !== true) {
                $this->setError($validateResult);
                return false;
            }

            // 处理文件上传
            $fileData = $this->handleFileUpload($form, $data);
            if ($fileData === false) {
                // 错误信息已在handleFileUpload中设置
                return false;
            }

            // 合并文件数据
            $submitData = array_merge($data, $fileData);

            // 保存提交的数据（这里可以根据需求修改为保存到专门的数据表）
            // 为了简化实现，我们直接返回处理后的数据
            return [
                'form_id' => $id,
                'data' => $submitData,
                'submitted_at' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 验证提交的数据
     * @param Form $form 表单对象
     * @param array $data 提交数据
     * @return true|string
     */
    protected function validateSubmitData($form, $data)
    {
        $fields = $form->getFieldsConfig();
        
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $fieldTitle = $field['title'];
            $isRequired = $field['required'] ?? false;
            
            // 检查必填字段
            if ($isRequired && (!isset($data[$fieldName]) || $data[$fieldName] === '')) {
                return $fieldTitle . '不能为空';
            }
            
            // 根据字段类型进行验证
            if (isset($data[$fieldName]) && $data[$fieldName] !== '') {
                $value = $data[$fieldName];
                
                switch ($field['type']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return $fieldTitle . '格式不正确';
                        }
                        break;
                    case 'number':
                        if (!is_numeric($value)) {
                            return $fieldTitle . '必须是数字';
                        }
                        break;
                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            return $fieldTitle . '格式不正确';
                        }
                        break;
                    case 'date':
                        if (!strtotime($value)) {
                            return $fieldTitle . '日期格式不正确';
                        }
                        break;
                }
            }
        }
        
        return true;
    }

    /**
     * 处理文件上传
     * @param Form $form 表单对象
     * @param array $data 提交数据
     * @return array|false
     */
    protected function handleFileUpload($form, $data)
    {
        $fileData = [];
        $fields = $form->getFieldsConfig();
        
        try {
            foreach ($fields as $field) {
                if (in_array($field['type'], ['file', 'image']) && isset($_FILES[$field['name']])) {
                    $file = $_FILES[$field['name']];
                    
                    // 检查文件是否上传成功
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $this->setError('文件上传失败：' . $field['title']);
                        return false;
                    }
                    
                    // 检查文件大小（这里设置为10MB限制）
                    if ($file['size'] > 10 * 1024 * 1024) {
                        $this->setError($field['title'] . '文件大小不能超过10MB');
                        return false;
                    }
                    
                    // 检查文件类型
                    if ($field['type'] === 'image') {
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!in_array($file['type'], $allowedTypes)) {
                            $this->setError($field['title'] . '只能上传jpg、png、gif格式的图片');
                            return false;
                        }
                    }
                    
                    // 生成文件名
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $extension;
                    
                    // 创建上传目录
                    $uploadPath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'uploads';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    
                    // 移动文件到上传目录
                    $targetPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $this->setError('文件上传失败：' . $field['title']);
                        return false;
                    }
                    
                    // 保存文件路径
                    $fileData[$field['name']] = '/uploads/' . $filename;
                }
            }
            
            return $fileData;
        } catch (\Exception $e) {
            $this->setError('文件上传异常：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 复制表单
     * @param int $id 原表单ID
     * @return bool|int
     */
    public function copyForm($id)
    {
        try {
            $form = $this->model->find($id);
            if (!$form) {
                $this->setError('表单不存在');
                return false;
            }

            // 复制数据
            $data = $form->toArray();
            unset($data['id']);
            unset($data['created_at']);
            unset($data['updated_at']);
            
            // 生成新名称
            $data['title'] = $data['title'] . ' (副本)';
            $data['name'] = $this->generateUniqueName($data['name']);

            return $this->add($data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 生成唯一的表单名称
     * @param string $originalName 原始名称
     * @return string
     */
    protected function generateUniqueName($originalName)
    {
        $baseName = $originalName;
        $counter = 1;
        
        while ($this->model->where('name', $originalName)->find()) {
            $originalName = $baseName . '_' . $counter;
            $counter++;
        }
        
        return $originalName;
    }
}