<?php

namespace app\controller;

use app\BaseController;
use app\service\FormService;
use think\response\Json;

/**
 * 表单构建器控制器
 * Class Form
 * @package app\controller
 */
class Form extends BaseController
{
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = new FormService();
    }

    /**
     * 获取表单列表
     * @return Json
     */
    public function getList(): Json
    {
        $params = $this->request->param();
        $result = $this->service->getFormList($params);
        return json($result);
    }

    /**
     * 获取表单详情
     * @param $id
     * @return Json
     */
    public function getInfo($id): Json
    {
        $result = $this->service->getFormInfo($id);
        if ($result) {
            return json(['code' => 1, 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 根据名称获取表单
     * @param $name
     * @return Json
     */
    public function getByName($name): Json
    {
        $result = $this->service->getFormByName($name);
        if ($result) {
            return json(['code' => 1, 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 新增表单
     * @return Json
     */
    public function create(): Json
    {
        $data = $this->request->post();
        $result = $this->service->createForm($data);
        if ($result) {
            return json(['code' => 1, 'msg' => '新增成功', 'data' => ['id' => $result]]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 更新表单
     * @param $id
     * @return Json
     */
    public function update($id): Json
    {
        $data = $this->request->put();
        $result = $this->service->updateForm($id, $data);
        if ($result) {
            return json(['code' => 1, 'msg' => '更新成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 删除表单
     * @param $id
     * @return Json
     */
    public function delete($id): Json
    {
        $result = $this->service->deleteForm($id);
        if ($result) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 批量删除表单
     * @return Json
     */
    public function batchDelete(): Json
    {
        $ids = $this->request->param('ids');
        if (empty($ids) || !is_array($ids)) {
            return json(['code' => 0, 'msg' => '请选择要删除的表单']);
        }
        $result = $this->service->batchDeleteForm($ids);
        if ($result) {
            return json(['code' => 1, 'msg' => '批量删除成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 修改表单状态
     * @param $id
     * @return Json
     */
    public function modifyStatus($id): Json
    {
        $status = $this->request->param('status');
        $result = $this->service->modifyFormStatus($id, $status);
        if ($result) {
            return json(['code' => 1, 'msg' => '状态修改成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 复制表单
     * @param $id
     * @return Json
     */
    public function copy($id): Json
    {
        $result = $this->service->copyForm($id);
        if ($result) {
            return json(['code' => 1, 'msg' => '复制成功', 'data' => ['id' => $result]]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 获取字段类型
     * @return Json
     */
    public function getFieldTypes(): Json
    {
        $result = $this->service->getFieldTypes();
        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 获取统计信息
     * @return Json
     */
    public function getStatistics(): Json
    {
        $result = $this->service->getStatistics();
        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 表单提交处理接口
     * @param $id
     * @return Json
     */
    public function submit($id): Json
    {
        $data = $this->request->post();
        $result = $this->service->submitForm($id, $data);
        if ($result) {
            return json(['code' => 1, 'msg' => '提交成功', 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }
}