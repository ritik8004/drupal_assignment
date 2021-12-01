import logger from '../../js/utilities/logger';

/**
 * Get endpoint for egift apis.
 *
 * @param {string} action
 *   Action for the endpoint.
 *
 * @returns {string}
 *   The api endpoint.
 */
const getApiEndpoint = (action) => {
  let endpoint = '';
  switch (action) {
    case 'getEgiftCardsProductList':
      endpoint = '/V1/products';
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Get query string for egift list api.
 *
 * @todo convert string to object of params.
 */
const getQueryStringForEgiftCards = () => ({
  'searchCriteria[pageSize]': 5,
  'searchCriteria[currentPage]': 1,
  'searchCriteria[filterGroups][0][filters][0][field]': 'visibility',
  'searchCriteria[filterGroups][0][filters][0][value]': 4,
  'searchCriteria[filterGroups][0][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][1][filters][0][field]': 'sku',
  'searchCriteria[filterGroups][1][filters][0][value]': 'giftcard_topup',
  'searchCriteria[filterGroups][1][filters][0][conditionType]': 'neq',
  'searchCriteria[filterGroups][2][filters][0][field]': 'type_id',
  'searchCriteria[filterGroups][2][filters][0][value]': 'virtual',
  'searchCriteria[filterGroups][2][filters][0][conditionType]': 'eq',
});

/**
 * Check if number is positive integer.
 */
const isInDesiredForm = (str) => {
  const n = Math.floor(Number(str));
  return n !== Infinity && String(n) === str && n >= 0;
};

/**
 * Get proxy / masked mdc url with media path.
 */
const getMdcMediaUrl = () => drupalSettings.egiftCard.mdcMediaUrl;

export {
  getApiEndpoint,
  getQueryStringForEgiftCards,
  getMdcMediaUrl,
  isInDesiredForm,
};
