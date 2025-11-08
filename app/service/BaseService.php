<?php

namespace app\service;

use think\facade\Log;
use think\exception\ValidateException;
use think\Response;

/**
 * 基础服务类
 * Class BaseService
 * @package app\service
 */
abstract class BaseService
{
    /**
     * 当前模型实例
     * @var \think\Model
     */
    protected $model;

    /**
     * 验证器类名
     * @var string
     */
    protected $validate;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 错误信息
     * @var string
     */
    protected $error = '';

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 设置错误信息
     * @param string $error
     * @return $this
     */
    protected function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 数据验证
     * @param array $data 验证数据
     * @param string|array $validate 验证器类名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|bool
     * @throws ValidateException
     */
    protected function validate($data, $validate = '', $message = [], $batch = false)
    {
        if (is_array($validate)) {
            $v = new \think\Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->validate;
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 获取列表数据
     * @param array $where 查询条件
     * @param array $options 查询选项
     * @return array
     */
    public function getList($where = [], $options = [])
    {
        try {
            $query = $this->model;

            // 搜索条件
            if (!empty($where)) {
                $query = $query->where($where);
            }

            // 搜索器
            if (!empty($options['search'])) {
                foreach ($options['search'] as $field => $value) {
                    $method = 'search' . ucfirst(camel_case($field)) . 'Attr';
                    if (method_exists($this->model, $method)) {
                        $this->model->$method($query, $value);
                    }
                }
            }

            // 排序
            if (!empty($options['sort'])) {
                $query = $query->order($options['sort']);
            } else {
                $query = $query->order('id', 'desc');
            }

            // 分页
            if (!empty($options['page'])) {
                $page = (int)($options['page'] ?? 1);
                $limit = (int)($options['limit'] ?? 15);
                $list = $query->paginate($limit, false, ['page' => $page]);
                return [
                    'items' => $list->items(),
                    'total' => $list->total(),
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => $list->lastPage()
                ];
            } else {
                // 不分页
                $limit = (int)($options['limit'] ?? 0);
                if ($limit > 0) {
                    $query = $query->limit($limit);
                }
                $list = $query->select();
                return [
                    'items' => $list,
                    'total' => count($list)
                ];
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('获取列表数据失败: ' . $e->getMessage());
            return ['items' => [], 'total' => 0];
        }
    }

    /**
     * 获取单条数据
     * @param mixed $id 主键值
     * @param array $with 关联预载入
     * @return array|null
     */
    public function getInfo($id, $with = [])
    {
        try {
            $query = $this->model;
            if (!empty($with)) {
                $query = $query->with($with);
            }
            $info = $query->find($id);
            return $info ? $info->toArray() : null;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('获取单条数据失败: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 添加数据
     * @param array $data 添加数据
     * @return bool|int
     */
    public function add($data)
    {
        try {
            // 数据验证
            if ($this->validate && !$this->validate($data)) {
                return false;
            }

            $result = $this->model->save($data);
            return $result ? $this->model->id : false;
        } catch (ValidateException $e) {
            $this->setError($e->getError());
            return false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('添加数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 编辑数据
     * @param mixed $id 主键值
     * @param array $data 编辑数据
     * @return bool
     */
    public function edit($id, $data)
    {
        try {
            // 数据验证
            if ($this->validate && !$this->validate($data, 'edit')) {
                return false;
            }

            $info = $this->model->find($id);
            if (!$info) {
                $this->setError('数据不存在');
                return false;
            }

            return $info->save($data) !== false;
        } catch (ValidateException $e) {
            $this->setError($e->getError());
            return false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('编辑数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 删除数据
     * @param mixed $id 主键值
     * @return bool
     */
    public function del($id)
    {
        try {
            $info = $this->model->find($id);
            if (!$info) {
                $this->setError('数据不存在');
                return false;
            }

            return $info->delete() !== false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('删除数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 批量删除
     * @param array $ids 主键值数组
     * @return bool
     */
    public function batchDel($ids)
    {
        try {
            if (empty($ids)) {
                $this->setError('请选择要删除的数据');
                return false;
            }

            return $this->model->whereIn('id', $ids)->delete() !== false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('批量删除数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 修改状态
     * @param mixed $id 主键值
     * @param int $status 状态值
     * @return bool
     */
    public function modifyStatus($id, $status)
    {
        try {
            $info = $this->model->find($id);
            if (!$info) {
                $this->setError('数据不存在');
                return false;
            }

            return $info->save(['status' => $status]) !== false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            Log::error('修改状态失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 构建查询条件
     * @param array $params 查询参数
     * @return array
     */
    protected function buildWhere($params)
    {
        $where = [];
        
        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', (int)$params['status']];
        }

        // 时间范围筛选
        if (!empty($params['start_time'])) {
            $where[] = ['created_at', '>=', $params['start_time']];
        }
        if (!empty($params['end_time'])) {
            $where[] = ['created_at', '<=', $params['end_time']];
        }

        return $where;
    }

    /**
     * 构建排序
     * @param array $params 查询参数
     * @return string
     */
    protected function buildSort($params)
    {
        $sort = 'id';
        $order = 'desc';

        if (!empty($params['sort'])) {
            $sortField = $params['sort'];
            if (in_array($sortField, $this->model->sortField ?? [])) {
                $sort = $sortField;
            }
        }

        if (!empty($params['order'])) {
            $order = strtolower($params['order']) === 'asc' ? 'asc' : 'desc';
        }

        return $sort . ' ' . $order;
    }
}