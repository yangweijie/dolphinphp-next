<?php

namespace app\controller;

use app\BaseController;
use app\service\TableService;
use think\response\Json;

/**
 * 表格构建器控制器
 * Class Table
 * @package app\controller
 */
class Table extends BaseController
{
    protected $service;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->service = new TableService();
    }

    /**
     * 获取表格列表
     * @return Json
     */
    public function getList(): Json
    {
        $params = $this->request->param();
        $result = $this->service->getTableList($params);
        return json($result);
    }

    /**
     * 获取表格详情
     * @param $id
     * @return Json
     */
    public function getInfo($id): Json
    {
        $result = $this->service->getTableInfo($id);
        if ($result) {
            return json(['code' => 1, 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 根据名称获取表格
     * @param $name
     * @return Json
     */
    public function getByName($name): Json
    {
        $result = $this->service->getTableByName($name);
        if ($result) {
            return json(['code' => 1, 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 新增表格
     * @return Json
     */
    public function create(): Json
    {
        $data = $this->request->post();
        $result = $this->service->createTable($data);
        if ($result) {
            return json(['code' => 1, 'msg' => '新增成功', 'data' => ['id' => $result]]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 更新表格
     * @param $id
     * @return Json
     */
    public function update($id): Json
    {
        $data = $this->request->put();
        $result = $this->service->updateTable($id, $data);
        if ($result) {
            return json(['code' => 1, 'msg' => '更新成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 删除表格
     * @param $id
     * @return Json
     */
    public function delete($id): Json
    {
        $result = $this->service->deleteTable($id);
        if ($result) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 批量删除表格
     * @return Json
     */
    public function batchDelete(): Json
    {
        $ids = $this->request->param('ids');
        if (empty($ids) || !is_array($ids)) {
            return json(['code' => 0, 'msg' => '请选择要删除的表格']);
        }
        $result = $this->service->batchDeleteTable($ids);
        if ($result) {
            return json(['code' => 1, 'msg' => '批量删除成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 修改表格状态
     * @param $id
     * @return Json
     */
    public function modifyStatus($id): Json
    {
        $status = $this->request->param('status');
        $result = $this->service->modifyTableStatus($id, $status);
        if ($result) {
            return json(['code' => 1, 'msg' => '状态修改成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 复制表格
     * @param $id
     * @return Json
     */
    public function copy($id): Json
    {
        $result = $this->service->copyTable($id);
        if ($result) {
            return json(['code' => 1, 'msg' => '复制成功', 'data' => ['id' => $result]]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 获取列类型
     * @return Json
     */
    public function getColumnTypes(): Json
    {
        $result = $this->service->getColumnTypes();
        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 获取数据源类型
     * @return Json
     */
    public function getDataSourceTypes(): Json
    {
        $result = $this->service->getDataSourceTypes();
        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 获取表格数据
     * @param $name
     * @return Json
     */
    public function getData($name): Json
    {
        $params = $this->request->param();
        $result = $this->service->getTableData($name, $params);
        if ($result) {
            return json(['code' => 1, 'data' => $result]);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
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
     * 更新表格行数据
     * @param $name
     * @param $id
     * @return Json
     */
    public function updateRow($name, $id): Json
    {
        $data = $this->request->put();
        $result = $this->service->updateTableRow($name, $id, $data);
        if ($result) {
            return json(['code' => 1, 'msg' => '更新成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }

    /**
     * 批量更新表格行数据
     * @param $name
     * @return Json
     */
    public function batchUpdateRows($name): Json
    {
        $data = $this->request->put();
        $result = $this->service->batchUpdateTableRows($name, $data);
        if ($result) {
            return json(['code' => 1, 'msg' => '批量更新成功']);
        }
        return json(['code' => 0, 'msg' => $this->service->getError()]);
    }
}