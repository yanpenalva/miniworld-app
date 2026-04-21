const taskRoutes = {
    children: [
      {
        path: '',
        name: 'listTasks',
        component: async () => import('@pages/admin/tasks/ListPage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Tarefas',
          icon: 'task',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
      {
        path: 'create',
        name: 'createTasks',
        component: async () => import('@pages/admin/tasks/CreatePage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Tarefas',
          icon: 'task',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
      {
        path: 'edit/:id',
        name: 'editTasks',
        component: async () => import('@pages/admin/tasks/EditPage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Tarefas',
          icon: 'task',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
      {
        path: 'show/:id',
        name: 'showTasks',
        component: async () => import('@pages/admin/tasks/ShowPage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Tarefas',
          icon: 'task',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
    ],
  };

  export default taskRoutes;
