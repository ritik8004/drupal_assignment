import { getAuraConfig } from './helper';

/**
 * Utility function to get transaction type options.
 */
function getTransactionTypeOptions() {
  return [
    { value: 'all', label: Drupal.t('All Transactions') },
    { value: 'online', label: Drupal.t('Online') },
    { value: 'offline', label: Drupal.t('Offline') },
  ];
}

/**
 * Utility function to get transaction date options.
 */
function getTransactionDateOptions() {
  const date = new Date();
  const dates = [];
  const { rewardActivityTimeLimit } = getAuraConfig();

  for (let i = 0; i < parseInt(rewardActivityTimeLimit, 10); i++) {
    const monthYear = date.toLocaleString('default', { month: 'short', year: 'numeric' });
    dates[i] = {
      value: monthYear,
      label: monthYear,
    };
    date.setMonth(date.getMonth() - 1);
  }

  return dates;
}

/**
 * Utility function to get date options default.
 */
function getTransactionDateOptionsDefaultValue(activity) {
  if (activity === null || Object.entries(activity).length === 0) {
    return { value: '', label: '' };
  }

  const defaultDate = new Date(Object.entries(activity)[0][1].date).toLocaleString(
    'default',
    { month: 'short', year: 'numeric' },
  );
  return { value: defaultDate, label: defaultDate };
}

/**
 * Utility function to format date.
 */
function formatDate(date) {
  return new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
}

export {
  getTransactionTypeOptions,
  getTransactionDateOptions,
  formatDate,
  getTransactionDateOptionsDefaultValue,
};
