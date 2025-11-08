<?php

namespace app\service;

use app\model\User;
use think\facade\Session;

/**
 * 用户服务类
 * Class UserService
 * @package app\service
 */
class UserService extends BaseService
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new User();
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool|array
     */
    public function login($username, $password)
    {
        try {
            // 查找用户
            $user = $this->model->where('username', $username)->whereOr('email', $username)->find();
            
            if (!$user) {
                $this->setError('用户不存在');
                return false;
            }

            // 验证密码
            if (!$user->verifyPassword($password)) {
                $this->setError('密码错误');
                return false;
            }

            // 检查状态
            if ($user->status != User::STATUS_ENABLE) {
                $this->setError('账号已被禁用');
                return false;
            }

            // 更新登录信息
            $user->last_login_time = date('Y-m-d H:i:s');
            $user->last_login_ip = request()->ip();
            $user->save();

            // 设置登录会话
            Session::set('user_id', $user->id);
            Session::set('user_info', $user->toArray());

            return $user->toArray();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 用户登出
     * @return bool
     */
    public function logout()
    {
        try {
            Session::delete('user_id');
            Session::delete('user_info');
            return true;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 检查是否登录
     * @return bool
     */
    public function isLogin()
    {
        return Session::has('user_id');
    }

    /**
     * 获取当前登录用户信息
     * @return array|null
     */
    public function getCurrentUser()
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }

        $userInfo = Session::get('user_info');
        if ($userInfo) {
            return $userInfo;
        }

        // 如果会话中没有，从数据库获取
        $user = $this->model->find($userId);
        if ($user) {
            Session::set('user_info', $user->toArray());
            return $user->toArray();
        }

        return null;
    }

    /**
     * 获取当前用户ID
     * @return int|null
     */
    public function getCurrentUserId()
    {
        return Session::get('user_id');
    }

    /**
     * 注册用户
     * @param array $data 用户数据
     * @return bool|int
     */
    public function register($data)
    {
        try {
            // 检查用户名是否已存在
            if ($this->model->where('username', $data['username'])->find()) {
                $this->setError('用户名已存在');
                return false;
            }

            // 检查邮箱是否已存在
            if ($this->model->where('email', $data['email'])->find()) {
                $this->setError('邮箱已存在');
                return false;
            }

            // 设置默认角色和状态
            $data['role'] = $data['role'] ?? User::ROLE_USER;
            $data['status'] = $data['status'] ?? User::STATUS_ENABLE;

            return $this->add($data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 修改密码
     * @param int $userId 用户ID
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return bool
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        try {
            $user = $this->model->find($userId);
            if (!$user) {
                $this->setError('用户不存在');
                return false;
            }

            // 验证旧密码
            if (!$user->verifyPassword($oldPassword)) {
                $this->setError('旧密码错误');
                return false;
            }

            // 更新密码
            $user->password = $newPassword;
            return $user->save() !== false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 重置密码
     * @param int $userId 用户ID
     * @param string $newPassword 新密码
     * @return bool
     */
    public function resetPassword($userId, $newPassword)
    {
        try {
            $user = $this->model->find($userId);
            if (!$user) {
                $this->setError('用户不存在');
                return false;
            }

            $user->password = $newPassword;
            return $user->save() !== false;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 获取用户列表（带搜索和分页）
     * @param array $params 查询参数
     * @return array
     */
    public function getUserList($params = [])
    {
        $where = $this->buildWhere($params);
        $options = [
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 15,
            'sort' => $this->buildSort($params)
        ];

        // 搜索条件
        if (!empty($params['search'])) {
            $options['search'] = [
                'username' => $params['search'],
                'email' => $params['search'],
                'role' => $params['search']
            ];
        }

        return $this->getList($where, $options);
    }

    /**
     * 检查用户权限
     * @param string $permission 权限标识
     * @return bool
     */
    public function checkPermission($permission)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // 管理员拥有所有权限
        if ($user['role'] == User::ROLE_ADMIN) {
            return true;
        }

        // 使用PermissionService检查权限
        $permissionService = new PermissionService();
        return $permissionService->checkRolePermission($user['role'], $permission);
    }

    /**
     * 获取用户统计信息
     * @return array
     */
    public function getStatistics()
    {
        try {
            $total = $this->model->count();
            $enable = $this->model->where('status', User::STATUS_ENABLE)->count();
            $disable = $this->model->where('status', User::STATUS_DISABLE)->count();
            $admin = $this->model->where('role', User::ROLE_ADMIN)->count();
            $user = $this->model->where('role', User::ROLE_USER)->count();

            return [
                'total' => $total,
                'enable' => $enable,
                'disable' => $disable,
                'admin' => $admin,
                'user' => $user
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'enable' => 0,
                'disable' => 0,
                'admin' => 0,
                'user' => 0
            ];
        }
    }
}