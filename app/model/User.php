<?php

namespace app\model;

use think\Model;

class User extends Model
{
    /**
     * 设置当前模型对应的完整数据表名称
     * @var string
     */
    protected $table = 'dp_users';

    /**
     * 设置主键名
     * @var string
     */
    protected $pk = 'id';

    /**
     * 自动时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 时间字段取出后的默认时间格式
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 字段类型映射
     * @var array
     */
    protected $type = [
        'id' => 'integer',
        'status' => 'integer',
        'last_login_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 可搜索字段
     * @var array
     */
    protected $searchField = ['username', 'email', 'role'];

    /**
     * 可排序字段
     * @var array
     */
    protected $sortField = ['id', 'username', 'last_login_time', 'created_at'];

    /**
     * 用户状态常量
     */
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

    /**
     * 用户角色常量
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    /**
     * 获取状态标签
     * @param int $status
     * @return string
     */
    public static function getStatusLabel($status = null)
    {
        $labels = [
            self::STATUS_DISABLE => '禁用',
            self::STATUS_ENABLE => '启用',
        ];
        return $status !== null ? ($labels[$status] ?? '未知') : $labels;
    }

    /**
     * 获取角色标签
     * @param string $role
     * @return string
     */
    public static function getRoleLabel($role = null)
    {
        $labels = [
            self::ROLE_ADMIN => '管理员',
            self::ROLE_USER => '普通用户',
        ];
        return $role !== null ? ($labels[$role] ?? '未知') : $labels;
    }

    /**
     * 设置密码
     * @param string $password
     * @return $this
     */
    public function setPasswordAttr($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 验证密码
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * 搜索器：用户名
     * @param \think\db\Query $query
     * @param string $value
     * @return void
     */
    public function searchUsernameAttr($query, $value)
    {
        $query->whereLike('username', '%' . $value . '%');
    }

    /**
     * 搜索器：邮箱
     * @param \think\db\Query $query
     * @param string $value
     * @return void
     */
    public function searchEmailAttr($query, $value)
    {
        $query->whereLike('email', '%' . $value . '%');
    }

    /**
     * 搜索器：角色
     * @param \think\db\Query $query
     * @param string $value
     * @return void
     */
    public function searchRoleAttr($query, $value)
    {
        $query->where('role', $value);
    }

    /**
     * 搜索器：状态
     * @param \think\db\Query $query
     * @param int $value
     * @return void
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }
}