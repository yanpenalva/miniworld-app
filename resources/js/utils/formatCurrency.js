export const formatCurrencyBRL = (value) => {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  const parsedValue = Number(value);

  if (Number.isNaN(parsedValue)) {
    return '-';
  }

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(parsedValue);
};
