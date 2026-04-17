import { ref } from 'vue';

export default function useTaskConfigListPage() {
  const columns = ref([
    {
      name: 'id',
      align: 'left',
      label: 'ID',
      field: 'id',
      sortable: true,
    },
    {
      name: 'description',
      required: true,
      label: 'Descrição',
      align: 'left',
      field: 'description',
      sortable: true,
    },
    {
      name: 'project',
      label: 'Projeto',
      align: 'left',
      field: (row) => row.project?.name ?? '-',
      sortable: false,
    },
    {
      name: 'predecessor',
      label: 'Predecessora',
      align: 'left',
      field: (row) => row.predecessor?.description ?? '-',
      sortable: false,
    },
    {
      name: 'status',
      label: 'Status',
      align: 'left',
      field: 'status',
      sortable: true,
    },
    {
      name: 'start_date',
      label: 'Data de Início',
      align: 'left',
      field: 'start_date',
      sortable: true,
    },
    {
      name: 'end_date',
      label: 'Data de Fim',
      align: 'left',
      field: 'end_date',
      sortable: true,
    },
    {
      name: 'action',
      label: 'Opções',
      align: 'center',
      field: (row) => row.id,
      format: (val) => `${val}`,
      methods: {
        onConsult: true,
        onEdit: true,
        onDelete: true,
      },
    },
  ]);

  return {
    columns,
  };
}
