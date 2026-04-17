import useProjectStore from '@/store/useProjectStore';
import useTaskStore from '@/store/useTaskStore';
import { computed, ref } from 'vue';

const createDefaultForm = () => ({
  description: '',
  project_id: null,
  predecessor_task_id: null,
  status: 'not_completed',
  start_date: null,
  end_date: null,
});

const normalizeDate = (value) => value || null;

const useTaskFormPage = () => {
  const taskStore = useTaskStore();
  const projectStore = useProjectStore();

  const form = ref(createDefaultForm());

  const projectOptions = computed(() =>
    (projectStore.getProjects ?? []).map((project) => ({
      label: project.name,
      value: project.id,
    })),
  );

  const predecessorOptions = computed(() =>
    (taskStore.getTasks ?? [])
      .filter((task) => task.id !== Number(form.value.id))
      .map((task) => ({
        label: task.description,
        value: task.id,
      })),
  );

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

  const loadTasks = async () => {
    await taskStore.list({
      limit: 100,
      page: 1,
      order: 'asc',
      column: 'description',
      search: '',
      paginated: true,
    });
  };

  const loadDependencies = async () => {
    await Promise.all([loadProjects(), loadTasks()]);
  };

  const fillForm = (task) => {
    form.value = {
      id: task?.id ?? null,
      description: task?.description ?? '',
      project_id: task?.project_id ?? null,
      predecessor_task_id: task?.predecessor_task_id ?? null,
      status: task?.status ?? 'not_completed',
      start_date: normalizeDate(task?.start_date),
      end_date: normalizeDate(task?.end_date),
    };
  };

  const buildPayload = () => ({
    description: form.value.description,
    project_id: form.value.project_id,
    predecessor_task_id: form.value.predecessor_task_id || null,
    status: form.value.status,
    start_date: normalizeDate(form.value.start_date),
    end_date: normalizeDate(form.value.end_date),
  });

  return {
    form,
    projectOptions,
    predecessorOptions,
    loadDependencies,
    fillForm,
    buildPayload,
  };
};

export default useTaskFormPage;
