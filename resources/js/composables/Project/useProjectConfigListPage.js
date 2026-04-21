import { ref } from 'vue';

export default function useProjectConfigListPage() {
  const columns = ref([
    {
      name: 'id',
      align: 'left',
      label: 'ID',
      field: 'id',
      sortable: true,
    },
    {
      name: 'name',
      required: true,
      label: 'Nome',
      align: 'left',
      field: 'name',
      format: (val) => `${val}`,
      sortable: true,
    },
    {
      name: 'description',
      label: 'Descrição',
      align: 'left',
      field: 'description',
      format: (val) => `${val ?? '-'}`,
      sortable: true,
    },
    {
      name: 'status',
      label: 'Status',
      align: 'left',
      field: 'status',
      sortable: true,
    },
    {
      name: 'budget',
      label: 'Orçamento',
      align: 'left',
      field: 'budget',
      sortable: false,
    },
    {
      name: 'progress',
      label: 'Progresso',
      align: 'left',
      field: 'progress',
      sortable: false,
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

  return { columns };
}
