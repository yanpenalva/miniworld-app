import useProjectStore from '@/store/useProjectStore';
import useTaskStore from '@/store/useTaskStore';
import notify from '@/utils/notify';
import { useQuasar } from 'quasar';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

const useTaskListPage = () => {
  const $q = useQuasar();
  const router = useRouter();
  const store = useTaskStore();
  const projectStore = useProjectStore();

  const loading = ref(false);
  const searchText = ref('');
  const statusFilter = ref('');
  const projectFilter = ref(null);
  const searchTimeout = ref(null);

  const pagination = ref({
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 15,
    rowsNumber: 0,
  });

  const statusOptions = [
    { label: 'Todos', value: '' },
    { label: 'Concluída', value: 'completed' },
    { label: 'Não concluída', value: 'not_completed' },
  ];

  const projectOptions = computed(() => [
    { label: 'Todos', value: null },
    ...(projectStore.getProjects ?? []).map((project) => ({
      label: project.name,
      value: project.id,
    })),
  ]);

  const rows = computed(() => store.getTasks ?? []);

  const syncListState = () => {
    pagination.value.rowsPerPage =
      store.getMeta?.per_page ?? pagination.value.rowsPerPage;
    pagination.value.page = store.getMeta?.current_page ?? pagination.value.page;
    pagination.value.rowsNumber = store.getMeta?.total ?? 0;
  };

  const resolveOrder = (descending) => (descending ? 'desc' : 'asc');

  const loadProjects = async () => {
    await projectStore.list({
      limit: 100,
      page: 1,
      order: 'asc',
      column: 'name',
      search: '',
      paginated: true,
    });
  };

  const listPage = async (params = {}) => {
    try {
      $q.loading.show();
      loading.value = true;

      await store.list({
        limit: params.limit ?? pagination.value.rowsPerPage,
        page: params.page ?? pagination.value.page,
        order: params.order ?? resolveOrder(pagination.value.descending),
        column: params.column ?? pagination.value.sortBy ?? 'id',
        search: params.search ?? searchText.value,
        status: params.status ?? statusFilter.value,
        project_id: params.project_id ?? projectFilter.value ?? undefined,
        paginated: true,
      });

      syncListState();
    } finally {
      loading.value = false;
      $q.loading.hide();
    }
  };

  const fetchTasks = async () => {
    await loadProjects();
    await listPage();
  };

  const handleSearch = (value) => {
    searchText.value = value ?? '';

    if (searchTimeout.value) {
      clearTimeout(searchTimeout.value);
    }

    searchTimeout.value = setTimeout(async () => {
      await listPage({
        limit: pagination.value.rowsPerPage,
        page: 1,
        order: resolveOrder(pagination.value.descending),
        column: pagination.value.sortBy,
        search: searchText.value,
      });
    }, 400);
  };

  const handleStatusFilter = async (value) => {
    statusFilter.value = value ?? '';
    await listPage({
      page: 1,
      status: statusFilter.value,
      project_id: projectFilter.value ?? undefined,
    });
  };

  const handleProjectFilter = async (value) => {
    projectFilter.value = value ?? null;
    await listPage({
      page: 1,
      status: statusFilter.value,
      project_id: projectFilter.value ?? undefined,
    });
  };

  const updatePagination = async (event) => {
    try {
      $q.loading.show();

      pagination.value.descending =
        event.pagination?.descending ?? pagination.value.descending;
      pagination.value.sortBy = event.pagination?.sortBy ?? pagination.value.sortBy;
      pagination.value.page = event.pagination?.page ?? pagination.value.page;
      pagination.value.rowsPerPage =
        event.pagination?.rowsPerPage ?? pagination.value.rowsPerPage;

      await listPage({
        limit: pagination.value.rowsPerPage,
        page: pagination.value.page,
        order: resolveOrder(pagination.value.descending),
        column: pagination.value.sortBy,
        search: searchText.value,
        status: statusFilter.value,
        project_id: projectFilter.value ?? undefined,
      });
    } finally {
      $q.loading.hide();
    }
  };

  const onEdit = (event) => {
    router.push({ name: 'editTasks', params: { id: event.id } });
  };

  const onConsult = (event) => {
    router.push({ name: 'showTasks', params: { id: event.id } });
  };

  const onDelete = async (event) => {
    try {
      $q.loading.show();
      await store.destroy(event.id);
      notify('Tarefa excluída com sucesso!');
    } finally {
      $q.loading.hide();
      await updatePagination({ pagination: pagination.value });
    }
  };

  const goToCreate = () => {
    router.push({ name: 'createTasks' });
  };

  return {
    loading,
    rows,
    pagination,
    statusOptions,
    projectOptions,
    statusFilter,
    projectFilter,
    fetchTasks,
    handleSearch,
    handleStatusFilter,
    handleProjectFilter,
    updatePagination,
    onEdit,
    onConsult,
    onDelete,
    goToCreate,
  };
};

export default useTaskListPage;
