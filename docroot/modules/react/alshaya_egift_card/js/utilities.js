import React from 'react';
import { callMagentoApi } from '../../js/utilities/requestHelper';
import PriceElement from '../../js/utilities/components/price/price-element';

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
 * Get Params for Top up card seach criteria.
 */
const getParamsForTopUpCardSearch = () => ({
  /* eslint-disable no-dupe-keys */
  'searchCriteria[pageSize]': 5,
  'searchCriteria[currentPage]': 1,
  'searchCriteria[filterGroups][0][filters][0][field]': 'visibility',
  'searchCriteria[filterGroups][0][filters][0][value]': 4,
  'searchCriteria[filterGroups][0][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][1][filters][0][field]': 'status',
  'searchCriteria[filterGroups][1][filters][0][value]': 1,
  'searchCriteria[filterGroups][1][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][1][filters][0][field]': 'sku',
  'searchCriteria[filterGroups][1][filters][0][value]': 'giftcard_topup',
  'searchCriteria[filterGroups][1][filters][0][conditionType]': 'eq',
  'searchCriteria[filterGroups][2][filters][0][field]': 'type_id',
  'searchCriteria[filterGroups][2][filters][0][value]': 'virtual',
  'searchCriteria[filterGroups][2][filters][0][conditionType]': 'eq',
  /* eslint-enable no-dupe-keys */
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

export {
  getQueryStringForEgiftCards,
  getParamsForTopUpCardSearch,
  getImageUrl,
};

/**
 * Provides the egift card response.
 *
 * @param {*} egiftBalance
 * @param {*} egiftCardNumber
 * @param {*} egiftCardValidity
 *
 */
export const egiftCardResponse = ({
  egiftBalance,
  egiftCardNumber,
  egiftCardValidity,
}) => (
  <div className="egift-balance-response">
    <p>
      {Drupal.t('Here is your current balance', {}, { context: 'egift' })}
    </p>
    <strong>
      <PriceElement amount={egiftBalance} />
    </strong>
    <p>
      {Drupal.t('for eGift card ending in ..', {}, { context: 'egift' })}
      {egiftCardNumber}
    </p>
    <p>
      {Drupal.t('Card valid up to ', {}, { context: 'egift' })}
      {egiftCardValidity}
    </p>
  </div>
);

/**
 * Provides the egift send otp api response.
 *
 * @param {*} egiftCardNumber
 *
 */
export const sendOtp = (egiftCardNumber) => {
  const data = {
    accountInfo: {
      cardNumber: egiftCardNumber,
      action: 'send_otp',
    },
  };
  // Send OTP to get card balance.
  return callMagentoApi('/V1/egiftcard/getBalance', 'POST', data);
};
