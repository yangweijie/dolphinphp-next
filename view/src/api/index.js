// API 基础配置
const API_BASE_URL = '/api';

// 创建 axios 实例
const request = axios.create({
    baseURL: API_BASE_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json'
    }
});

// 请求拦截器
request.interceptors.request.use(
    config => {
        // 添加 token
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

// 响应拦截器
request.interceptors.response.use(
    response => {
        const res = response.data;
        if (res.code !== 200) {
            ElMessage.error(res.msg || '请求失败');
            // 处理特殊错误码
            if (res.code === 401) {
                // 清除 token 并跳转到登录页
                localStorage.removeItem('token');
                router.push('/login');
            }
            return Promise.reject(new Error(res.msg || 'Error'));
        } else {
            return res;
        }
    },
    error => {
        ElMessage.error(error.message || '网络错误');
        return Promise.reject(error);
    }
);

// API 方法封装
const api = {
    // 用户相关
    user: {
        login: (data) => request.post('/user/login', data),
        logout: () => request.post('/user/logout'),
        getCurrent: () => request.get('/user/getCurrentUser'),
        getList: (params) => request.get('/user/getList', { params }),
        getInfo: (id) => request.get(`/user/getInfo?id=${id}`),
        create: (data) => request.post('/user/create', data),
        update: (data) => request.post('/user/update', data),
        delete: (id) => request.post('/user/delete', { id }),
        batchDelete: (ids) => request.post('/user/batchDelete', { ids }),
        modifyStatus: (data) => request.post('/user/modifyStatus', data),
        changePassword: (data) => request.post('/user/changePassword', data),
        getRoles: () => request.get('/user/getRoles'),
        getStatusList: () => request.get('/user/getStatusList'),
        getStatistics: () => request.get('/user/getStatistics')
    },

    // 表单相关
    form: {
        getList: (params) => request.get('/form/getList', { params }),
        getInfo: (id) => request.get(`/form/getInfo?id=${id}`),
        getByName: (name) => request.get(`/form/getByName?name=${name}`),
        create: (data) => request.post('/form/create', data),
        update: (data) => request.post('/form/update', data),
        delete: (id) => request.post('/form/delete', { id }),
        batchDelete: (ids) => request.post('/form/batchDelete', { ids }),
        modifyStatus: (data) => request.post('/form/modifyStatus', data),
        copy: (id) => request.post('/form/copy', { id }),
        getFieldTypes: () => request.get('/form/getFieldTypes'),
        getStatistics: () => request.get('/form/getStatistics')
    },

    // 表格相关
    table: {
        getList: (params) => request.get('/table/getList', { params }),
        getInfo: (id) => request.get(`/table/getInfo?id=${id}`),
        getByName: (name) => request.get(`/table/getByName?name=${name}`),
        create: (data) => request.post('/table/create', data),
        update: (data) => request.post('/table/update', data),
        delete: (id) => request.post('/table/delete', { id }),
        batchDelete: (ids) => request.post('/table/batchDelete', { ids }),
        modifyStatus: (data) => request.post('/table/modifyStatus', data),
        copy: (id) => request.post('/table/copy', { id }),
        getColumnTypes: () => request.get('/table/getColumnTypes'),
        getDataSourceTypes: () => request.get('/table/getDataSourceTypes'),
        getData: (id, params) => request.get(`/table/getData?id=${id}`, { params }),
        getStatistics: () => request.get('/table/getStatistics')
    }
};

// 工具函数
const utils = {
    // 格式化日期
    formatDate: (date, format = 'YYYY-MM-DD HH:mm:ss') => {
        if (!date) return '';
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day)
            .replace('HH', hours)
            .replace('mm', minutes)
            .replace('ss', seconds);
    },

    // 深拷贝
    deepClone: (obj) => {
        return JSON.parse(JSON.stringify(obj));
    },

    // 生成唯一ID
    generateId: () => {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },

    // 验证邮箱
    validateEmail: (email) => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // 验证手机号
    validatePhone: (phone) => {
        const re = /^1[3-9]\d{9}$/;
        return re.test(phone);
    }
};

// 全局挂载
window.api = api;
window.utils = utils;