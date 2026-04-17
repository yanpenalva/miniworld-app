import useProjectStore from '@/store/useProjectStore';
import notify from '@/utils/notify';
import { useQuasar } from 'quasar';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const useProjectListPage = () => {
  const $q = useQuasar();
  const router = useRouter();
  const store = useProjectStore();

  const loading = ref(false);
  const rows = ref([]);
  const searchText = ref('');
  const searchTimeout = ref(null);

  const pagination = ref({
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 15,
    rowsNumber: 0,
  });

  const syncListState = () => {
    rows.value = store.getProjects;
    pagination.value.rowsPerPage =
      store.getMeta?.per_page ?? pagination.value.rowsPerPage;
    pagination.value.page = store.getMeta?.current_page ?? pagination.value.page;
    pagination.value.rowsNumber = store.getMeta?.total ?? 0;
  };

  const resolveOrder = (descending) => (descending ? 'desc' : 'asc');

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
        paginated: true,
      });

      syncListState();
    } finally {
      loading.value = false;
      $q.loading.hide();
    }
  };

  const fetchProjects = async () => {
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
      });
    } finally {
      $q.loading.hide();
    }
  };

  const onEdit = (event) => {
    router.push({ name: 'editProjects', params: { id: event.id } });
  };

  const onConsult = (event) => {
    router.push({ name: 'showProjects', params: { id: event.id } });
  };

  const onDelete = async (event) => {
    try {
      $q.loading.show();
      await store.destroy(event.id);
      notify('Projeto excluído com sucesso!');
    } finally {
      $q.loading.hide();
      await updatePagination({ pagination: pagination.value });
    }
  };

  const goToCreate = () => {
    router.push({ name: 'createProjects' });
  };

  return {
    loading,
    rows,
    pagination,
    fetchProjects,
    handleSearch,
    updatePagination,
    onEdit,
    onConsult,
    onDelete,
    goToCreate,
  };
};

export default useProjectListPage;
