// 状态管理
const store = Vuex.createStore({
    state: {
        // 用户信息
        userInfo: {
            id: 0,
            username: '',
            email: '',
            role: '',
            status: 0
        },
        
        // 系统配置
        config: {
            siteName: 'DolphinPHP',
            version: '1.0.0'
        },
        
        // 全局状态
        loading: false,
        sidebarCollapsed: false,
        
        // 缓存数据
        cache: {
            users: [],
            forms: [],
            tables: []
        }
    },
    
    mutations: {
        // 设置用户信息
        SET_USER_INFO(state, userInfo) {
            state.userInfo = { ...state.userInfo, ...userInfo };
        },
        
        // 清除用户信息
        CLEAR_USER_INFO(state) {
            state.userInfo = {
                id: 0,
                username: '',
                email: '',
                role: '',
                status: 0
            };
        },
        
        // 设置加载状态
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        
        // 设置侧边栏状态
        SET_SIDEBAR_COLLAPSED(state, collapsed) {
            state.sidebarCollapsed = collapsed;
        },
        
        // 设置缓存数据
        SET_CACHE(state, { key, data }) {
            state.cache[key] = data;
        },
        
        // 清除缓存
        CLEAR_CACHE(state, key) {
            if (key) {
                state.cache[key] = [];
            } else {
                state.cache = {
                    users: [],
                    forms: [],
                    tables: []
                };
            }
        }
    },
    
    actions: {
        // 获取用户信息
        async getUserInfo({ commit }) {
            try {
                const res = await api.user.getCurrent();
                commit('SET_USER_INFO', res.data);
                return res.data;
            } catch (error) {
                console.error('获取用户信息失败:', error);
                throw error;
            }
        },
        
        // 登录
        async login({ commit }, credentials) {
            try {
                const res = await api.user.login(credentials);
                const { token, userInfo } = res.data;
                
                // 保存 token
                localStorage.setItem('token', token);
                
                // 设置用户信息
                commit('SET_USER_INFO', userInfo);
                
                return res.data;
            } catch (error) {
                console.error('登录失败:', error);
                throw error;
            }
        },
        
        // 登出
        async logout({ commit }) {
            try {
                await api.user.logout();
            } catch (error) {
                console.error('登出失败:', error);
            } finally {
                // 清除本地数据
                localStorage.removeItem('token');
                commit('CLEAR_USER_INFO');
                commit('CLEAR_CACHE');
            }
        },
        
        // 更新用户信息
        async updateUserInfo({ commit, state }, userData) {
            try {
                const res = await api.user.update(userData);
                commit('SET_USER_INFO', { ...state.userInfo, ...userData });
                return res.data;
            } catch (error) {
                console.error('更新用户信息失败:', error);
                throw error;
            }
        }
    },
    
    getters: {
        // 是否已登录
        isLoggedIn: state => !!state.userInfo.id,
        
        // 是否是管理员
        isAdmin: state => state.userInfo.role === 'admin',
        
        // 获取用户信息
        userInfo: state => state.userInfo,
        
        // 获取加载状态
        loading: state => state.loading,
        
        // 获取缓存数据
        getCache: state => key => state.cache[key] || []
    }
});

// 全局挂载
window.store = store;