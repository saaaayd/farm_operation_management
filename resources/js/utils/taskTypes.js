export const BASE_TASK_TYPES = [
  { value: 'land_preparation', label: 'Land Preparation' },
  { value: 'seedling', label: 'Seedling Management' },
  { value: 'transplanting', label: 'Transplanting' },
  { value: 'fertilizing', label: 'Fertilizing' },
  { value: 'weeding', label: 'Weeding' },
  { value: 'pesticide_application', label: 'Pesticide Application' },
  { value: 'water_management', label: 'Water Management' },
  { value: 'harvesting', label: 'Harvesting' },
  { value: 'maintenance', label: 'Equipment & Field Maintenance' },
];

const titleCase = (value = '') =>
  value
    .toString()
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
    .trim();

export const getTaskTypeLabel = (value) => {
  if (!value) return '';
  const base = BASE_TASK_TYPES.find((type) => type.value === value);
  return base ? base.label : titleCase(value);
};

export const buildTaskTypeOptions = (tasks = [], { includeBase = true } = {}) => {
  const optionMap = new Map();

  tasks.forEach((task) => {
    if (!task?.task_type) return;
    const normalized = task.task_type;
    optionMap.set(normalized, getTaskTypeLabel(normalized));
  });

  if (includeBase || optionMap.size === 0) {
    BASE_TASK_TYPES.forEach((type) => {
      if (!optionMap.has(type.value)) {
        optionMap.set(type.value, type.label);
      }
    });
  }

  return Array.from(optionMap.entries()).map(([value, label]) => ({
    value,
    label,
  }));
};



