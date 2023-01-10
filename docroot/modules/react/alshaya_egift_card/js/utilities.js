/**
 * Get query string for e-Gift list api.
 *
 */
const getQueryStringForEgiftCards = () => ({
  'searchCriteria[pageSize]': 5,
  'searchCriteria[currentPage]': 1,
  'searchCriteria[filterGroups][0][filters][0][field]': 'visibility',
  'searchCriteria[filterGroups][0][filters][0][value]': 4,
  'searchCriteria[filterGroups][0][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][1][filters][0][field]': 'status',
  'searchCriteria[filterGroups][1][filters][0][value]': 1,
  'searchCriteria[filterGroups][1][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][2][filters][0][field]': 'sku',
  'searchCriteria[filterGroups][2][filters][0][value]': 'giftcard_topup',
  'searchCriteria[filterGroups][2][filters][0][conditionType]': 'neq',
  'searchCriteria[filterGroups][3][filters][0][field]': 'type_id',
  'searchCriteria[filterGroups][3][filters][0][value]': 'virtual',
  'searchCriteria[filterGroups][3][filters][0][conditionType]': 'eq',
});

/**
 * Get Params for Top up card seach criteria.
 */
const getParamsForTopUpCardSearch = () => ({
  'searchCriteria[pageSize]': 5,
  'searchCriteria[currentPage]': 1,
  'searchCriteria[filterGroups][0][filters][0][field]': 'visibility',
  'searchCriteria[filterGroups][0][filters][0][value]': 4,
  'searchCriteria[filterGroups][0][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][1][filters][0][field]': 'status',
  'searchCriteria[filterGroups][1][filters][0][value]': 1,
  'searchCriteria[filterGroups][1][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][2][filters][0][field]': 'sku',
  'searchCriteria[filterGroups][2][filters][0][value]': 'giftcard_topup',
  'searchCriteria[filterGroups][2][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][3][filters][0][field]': 'type_id',
  'searchCriteria[filterGroups][3][filters][0][value]': 'virtual',
  'searchCriteria[filterGroups][3][filters][0][conditionType]': 'eq',
});

/**
 * Get proxy / masked mdc url with media path.
 */
const getImageUrl = (customAttributes, type) => {
  let url = '';
  if (customAttributes.length > 0) {
    customAttributes.forEach((attribute) => {
      if (typeof attribute.attribute_code !== 'undefined' && attribute.attribute_code === type) {
        url = attribute.value;
      }
    });
  }
  return url;
};

const getTextAreaMaxLength = () => {
  const { textAreaMaxlength } = drupalSettings.egiftCard;
  if (textAreaMaxlength !== null) {
    return textAreaMaxlength;
  }

  return 200;
};

export {
  getQueryStringForEgiftCards,
  getParamsForTopUpCardSearch,
  getImageUrl,
  getTextAreaMaxLength,
};
