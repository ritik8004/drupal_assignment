import { getAuraConfig } from './helper';

/**
 * Utility function to get transaction type options.
 */
function getTransactionTypeOptions() {
  return [
    { value: 'all', label: Drupal.t('All Transactions') },
    { value: 'K', label: Drupal.t('Online') },
    { value: 'V', label: Drupal.t('Instore') },
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
    dates[i] = {
      value: `1 ${date.toLocaleString('default', { month: 'short', year: 'numeric' })}`,
      label: date.toLocaleString(drupalSettings.path.currentLanguage, { month: 'short', year: 'numeric' }),
    };
    date.setMonth(date.getMonth() - 1);
  }

  return dates;
}

/**
 * Utility function to format date.
 */
function formatDate(date, type) {
  // eg. 2020-12-01
  if (type === 'YYYY-MM-DD') {
    return new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
  }

  // eg. 2020-12-01T23:59:59
  if (type === 'YYYY-MM-DDT') {
    // Date in this format `2021-12-14` is considered as midnight that day so if we want to
    // include all the transactions done on that particular day as well, we have to provide
    // time as well something like `T23:59:59`.
    const formatedDate = new Date(
      date.getTime() - (date.getTimezoneOffset() * 60000),
    ).setUTCHours(23, 59, 59);
    return new Date(formatedDate).toISOString();
  }

  // eg. Feb 2021
  if (type === 'Mon-YYYY') {
    return new Date(date).toLocaleString(drupalSettings.path.currentLanguage, { month: 'short', year: 'numeric' });
  }

  // eg. 02 Feb 2021
  if (type === 'DD-Mon-YYYY') {
    return new Date(date).toLocaleString(drupalSettings.path.currentLanguage, { day: '2-digit', month: 'short', year: 'numeric' });
  }

  return date;
}

/**
 * Utility function to get date options default.
 */
function getTransactionDateOptionsDefaultValue(fromDate) {
  if (fromDate.length === 0) {
    return getTransactionDateOptions()[0];
  }
  const formatedDate = formatDate(fromDate, 'Mon-YYYY');
  return { value: formatedDate, label: formatedDate };
}

/**
 * Utility function to get brand options.
 */
function getTransactionBrandOptions() {
  const brandOptions = [];
  if (typeof drupalSettings.aura !== 'undefined'
    && ({}).hasOwnProperty.call(drupalSettings.aura, 'allBrands')) {
    Object.entries(drupalSettings.aura.allBrands).forEach(([key, value]) => {
      brandOptions.push({
        value: key,
        label: value,
      });
    });
  }

  return brandOptions;
}

export {
  getTransactionTypeOptions,
  getTransactionDateOptions,
  formatDate,
  getTransactionDateOptionsDefaultValue,
  getTransactionBrandOptions,
};
