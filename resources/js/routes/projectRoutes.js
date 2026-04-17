const projectRoutes = {
    children: [
      {
        path: '',
        name: 'listProjects',
        component: async () => import('@pages/admin/projects/ListPage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Projetos',
          icon: 'folder',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
      {
        path: 'create',
        name: 'createProjects',
        component: async () => import('@pages/admin/projects/CreatePage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Projetos',
          icon: 'folder',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
      {
        path: 'edit/:id',
        name: 'editProjects',
        component: async () => import('@pages/admin/projects/EditPage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Projetos',
          icon: 'folder',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
      {
        path: 'show/:id',
        name: 'showProjects',
        component: async () => import('@pages/admin/projects/ShowPage.vue'),
        meta: {
          requiresAuth: true,
          module: 'Projetos',
          icon: 'folder',
          iconColor: '#344955',
          iconBg: '#FFAA30',
        },
      },
    ],
  };

  export default projectRoutes;
