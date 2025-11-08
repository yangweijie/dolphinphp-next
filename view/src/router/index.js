// 路由配置
const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('../views/Home.vue')
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/Login.vue')
    },
    {
        path: '/forms',
        name: 'FormList',
        component: () => import('../views/form/List.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/forms/create',
        name: 'FormCreate',
        component: () => import('../views/form/Create.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/forms/edit/:id',
        name: 'FormEdit',
        component: () => import('../views/form/Edit.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/forms/preview/:id',
        name: 'FormPreview',
        component: () => import('../views/form/Preview.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tables',
        name: 'TableList',
        component: () => import('../views/table/List.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tables/create',
        name: 'TableCreate',
        component: () => import('../views/table/Create.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tables/edit/:id',
        name: 'TableEdit',
        component: () => import('../views/table/Edit.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/tables/preview/:id',
        name: 'TablePreview',
        component: () => import('../views/table/Preview.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/users',
        name: 'UserList',
        component: () => import('../views/user/List.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/users/create',
        name: 'UserCreate',
        component: () => import('../views/user/Create.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/users/edit/:id',
        name: 'UserEdit',
        component: () => import('../views/user/Edit.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('../views/Profile.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('../views/NotFound.vue')
    }
];

// 创建路由实例
const router = VueRouter.createRouter({
    history: VueRouter.createWebHashHistory(),
    routes
});

// 路由守卫
router.beforeEach(async (to, from, next) => {
    // 检查是否需要认证
    if (to.meta.requiresAuth) {
        const token = localStorage.getItem('token');
        
        if (!token) {
            // 没有 token，跳转到登录页
            next('/login');
            return;
        }
        
        // 检查用户信息是否存在
        if (!store.getters.isLoggedIn) {
            try {
                await store.dispatch('getUserInfo');
            } catch (error) {
                // 获取用户信息失败，清除 token 并跳转到登录页
                localStorage.removeItem('token');
                next('/login');
                return;
            }
        }
        
        // 检查是否需要管理员权限
        if (to.meta.requiresAdmin && !store.getters.isAdmin) {
            ElMessage.error('您没有权限访问该页面');
            next('/');
            return;
        }
    }
    
    next();
});

// 路由后置钩子
router.afterEach((to, from) => {
    // 设置页面标题
    const title = to.name || 'DolphinPHP';
    document.title = `${title} - DolphinPHP`;
    
    // 更新活跃菜单
    if (window.app && window.app.$data) {
        window.app.$data.activeIndex = to.path;
    }
});

// 全局挂载
window.router = router;