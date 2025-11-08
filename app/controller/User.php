<?php

namespace app\controller;

use app\BaseController;
use app\service\UserService;
use think\response\Json;

/**
 * 用户控制器
 * Class User
 * @package app\controller
 */
class User extends BaseController
{
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = new UserService();
    }

    /**
     * 用户登录
     * @return Json
     */
    public function login(): Json
    {
        $data = $this->request->post();
        $result = $this->service->login($data['username'], $data['password']);
        if ($result) {
            // 生成JWT Token
            $jwtService = new \app\service\JwtService();
            $token = $jwtService->createToken($result);
            
            return json([
                'code' => 1, 
                'msg' => '登录成功', 
                'data' => [
                    'user' => $result,
                    'token' => $token
                ]
            ]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 用户退出
     * @return Json
     */
    public function logout(): Json
    {
        $this->service->logout();
        return json(['code' => 1, 'msg' => '退出成功']);
    }

    /**
     * 获取当前登录用户信息
     * @return Json
     */
    public function getCurrentUser(): Json
    {
        $user = $this->service->getCurrentUser();
        if ($user) {
            return json(['code' => 1, 'data' => $user]);
        }
        return json(['code' => 0, 'msg' => '用户未登录']);
    }

    /**
     * 获取用户列表
     * @return Json
     */
    public function getList(): Json
    {
        $params = $this->request->param();
        $result = $this->service->getUserList($params);
        return json($result);
    }

    /**
     * 获取用户详情
     * @param $id
     * @return Json
     */
    public function getInfo($id): Json
    {
        $result = $this->service->getUserInfo($id);
        if ($result) {
            return json(['code' => 1, 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 新增用户
     * @return Json
     */
    public function create(): Json
    {
        $data = $this->request->post();
        $result = $this->service->createUser($data);
        if ($result) {
            return json(['code' => 1, 'msg' => '新增成功', 'data' => ['id' => $result]]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 更新用户
     * @param $id
     * @return Json
     */
    public function update($id): Json
    {
        $data = $this->request->put();
        $result = $this->service->updateUser($id, $data);
        if ($result) {
            return json(['code' => 1, 'msg' => '更新成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 删除用户
     * @param $id
     * @return Json
     */
    public function delete($id): Json
    {
        $result = $this->service->deleteUser($id);
        if ($result) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 批量删除用户
     * @return Json
     */
    public function batchDelete(): Json
    {
        $ids = $this->request->param('ids');
        if (empty($ids) || !is_array($ids)) {
            return json(['code' => 0, 'msg' => '请选择要删除的用户']);
        }
        $result = $this->service->batchDeleteUser($ids);
        if ($result) {
            return json(['code' => 1, 'msg' => '批量删除成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 修改用户状态
     * @param $id
     * @return Json
     */
    public function modifyStatus($id): Json
    {
        $status = $this->request->param('status');
        $result = $this->service->modifyUserStatus($id, $status);
        if ($result) {
            return json(['code' => 1, 'msg' => '状态修改成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 修改密码
     * @return Json
     */
    public function changePassword(): Json
    {
        $data = $this->request->post();
        $result = $this->service->changePassword($data['old_password'], $data['new_password']);
        if ($result) {
            return json(['code' => 1, 'msg' => '密码修改成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 获取用户角色列表
     * @return Json
     */
    public function getRoles(): Json
    {
        $result = $this->service->getRoles();
        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 获取用户状态列表
     * @return Json
     */
    public function getStatusList(): Json
    {
        $result = $this->service->getStatusList();
        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 获取用户统计信息
     * @return Json
     */
    public function getStatistics(): Json
    {
        $result = $this->service->getStatistics();
        return json(['code' => 1, 'data' => $result]);
    }
}