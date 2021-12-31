import React from 'react';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import logger from '../../../js/utilities/logger';
import dispatchCustomEvent from '../../../js/utilities/events';
import { removeFullScreenLoader } from '../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import isEgiftCardEnabled from '../../../js/utilities/egiftCardHelper';

/**
 * Provides the egift card header.
 *
 * @param {*} egiftHeading
 * @param {*} egiftSubHeading
 */
export const egiftCardHeader = ({
  egiftHeading,
  egiftSubHeading,
}) => (
  <div className="egift-header-wrapper">
    <div className="egift-header-title">
      {egiftHeading}
    </div>
    <div className="egift-header-subtitle">
      {egiftSubHeading}
    </div>
  </div>
);

/**
 * Provides different form element.
 *
 * @param {*} type
 * @param {*} name
 * @param {*} label
 * @param {*} className
 * @param {*} buttonText
 */
export const egiftFormElement = ({
  type = '',
  name = '',
  label = '',
  className = '',
  buttonText = '',
  value = '',
  disabled = false,
}) => {
  const handleEvent = (e) => {
    if (e.currentTarget.value.length > 0) {
      e.currentTarget.classList.add('focus');
    } else {
      e.currentTarget.classList.remove('focus');
    }
  };

  let focusClass = '';
  if (value !== undefined && value !== '') {
    focusClass += ' focus';
  }

  // Separate template based on type.
  let rtnTemplate = '';
  switch (type) {
    case 'submit':
      rtnTemplate = (
        <input
          className="egift-button"
          id={`egift-${name}`}
          type={type}
          value={Drupal.t(buttonText, {}, { context: 'egift' })}
          disabled={disabled}
        />
      );
      break;

    case 'number':
      rtnTemplate = (
        <div className={`egift-type-${type} spc-type-textfield egift-input-item`}>
          <input
            type={type}
            name={`egift_${name}`}
            className={`${className} ${focusClass}`}
            defaultValue={value}
            disabled={disabled}
            onBlur={(e) => handleEvent(e)}
          />
          <div className="c-input__bar" />
          <label>{label}</label>
          <span className="open-amount-currency">
            { drupalSettings.alshaya_spc.currency_config.currency_code }
          </span>
          <div id={`egift_${name}_error`} className="error" />
        </div>
      );
      break;

    default:
      rtnTemplate = (
        <div className={`egift-type-${type} spc-type-textfield`}>
          <input
            type={type}
            name={`egift_${name}`}
            className={className}
            defaultValue={value}
            disabled={disabled}
            onBlur={(e) => handleEvent(e)}
          />
          <div className="c-input__bar" />
          <label>{label}</label>
          <div id={`egift_${name}_error`} className="error" />
        </div>
      );
  }

  return rtnTemplate;
};

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 * @param {object} params
 *   The object with cartId, itemId.
 *
 * @returns {string}
 *   The api endpoint.
 */
export const getApiEndpoint = (action, params = {}) => {
  let endpoint = '';
  switch (action) {
    case 'eGiftGetBalance':
      endpoint = '/V1/egiftcard/getBalance';
      break;

    case 'eGiftRedemption':
      endpoint = '/V1/egiftcard/transact';
      break;

    case 'eGiftHpsSearch':
      endpoint = `/V1/egiftcard/hps-search/email/${params.email}`;
      break;

    case 'eGiftHpsCustomerData':
      endpoint = '/V1/customers/hpsCustomerData';
      break;

    case 'eGiftLinkCard':
      endpoint = '/V1/egiftcard/link';
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets egift response from magento api endpoint.
 *
 * @param {string} action
 *   Callname for the API.
 * @param {string} method
 *   The request method.
 * @param {object} postData
 *   The object containing post data
 * @param {object} params
 *   The object containing param info.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const callEgiftApi = (action, method, postData, params) => {
  const endpoint = getApiEndpoint(action, params);
  return callMagentoApi(endpoint, method, postData);
};

/**
 * Performs egift redemption.
 *
 * @param {int} quoteId
 *   Cart id.
 * @param {int} updateAmount
 *   Amount needs to be redeemed.
 * @param {int} egiftCardNumber
 *   Card number needs to be redeemed.
 * @param {string} cardType
 *   Card type to identify from which it redeemed.
 *
 * @returns {object}
 *   The response object.
 */
export const performRedemption = (quoteId, updateAmount, egiftCardNumber, cardType) => {
  const postData = {
    redeem_points: {
      action: 'set_points',
      quote_id: quoteId,
      amount: updateAmount,
      card_number: egiftCardNumber,
      payment_method: 'hps_payment',
      card_type: cardType,
    },
  };

  // Invoke the redemption API to update the redeem amount.
  const response = callEgiftApi('eGiftRedemption', 'POST', postData);
  return response;
};

/**
 * Triggers custom event to update price summary block.
 */
export const updatePriceSummaryBlock = () => {
  const cartData = window.commerceBackend.getCart(true);
  if (cartData instanceof Promise) {
    cartData.then((data) => {
      if (data.data !== undefined && data.data.error === undefined) {
        if (data.status === 200) {
          // Update Egift card line item.
          dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
          removeFullScreenLoader();
        }
      }
    });
  }
};

/**
 * Checks if cart contains atleast a single normal product.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @return {boolean}
 *   true if it contain's atleast one normal product else false.
 */
export const cartContainsAnyNormalProduct = (cart) => {
  // A flag to keep track of the non-virtual products.
  let isNonVirtual = false;
  Object.values(cart.items).forEach((item) => {
    // Return if we have already marked a non virtual product.
    if (isNonVirtual || !hasValue(item)) {
      return;
    }
    // If there is no product type for the cart item then it's non virtual
    // product.
    if ((hasValue(item.product_type) && item.product_type !== 'virtual')
      || (Object.prototype.hasOwnProperty.call(item, 'isEgiftCard') && !item.isEgiftCard)) {
      isNonVirtual = true;
    }
  });

  return isNonVirtual;
};

/**
 * Checks if cart has only egift card products or other products as well.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @return {boolean}
 *   true if it contain's virtual product else false.
 */
export const cartContainsOnlyVirtualProduct = (cart) => {
  // If egift card is not enabled then return true.
  if (!isEgiftCardEnabled()) {
    return false;
  }

  return !cartContainsAnyNormalProduct(cart);
};

/**
 * Checks if redemptions is performed or not.
 *
 * @param {object} cart
 *   The cart object.
 * @param {string} redemptionType
 *   The type of redemption done.
 *
 * @return {boolean}
 *   true if egift redemption is done by guest else false.
 */
export const isEgiftRedemptionDone = (cart, redemptionType = 'guest') => {
  if (hasValue(cart.totals)) {
    const { egiftRedeemedAmount, egiftRedemptionType } = cart.totals;

    if (hasValue(egiftRedeemedAmount)
      && hasValue(egiftRedemptionType)
      && egiftRedemptionType === redemptionType) {
      return true;
    }
  }

  return false;
};

/**
 * Utility function to check if given payment method is unsupported with egift.
 */
export const isEgiftUnsupportedPaymentMethod = (paymentMethod) => {
  const { notSupportedPaymentMethods } = drupalSettings.egiftCard;
  return paymentMethod in notSupportedPaymentMethods;
};

/**
 * Checks if the full payment is done by egift or not.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @return {boolean}
 *   Returns True if full payment is done by egift else false.
 */
export const isFullPaymentDoneByEgift = (cart) => {
  if (hasValue(cart.totals)) {
    const {
      egiftRedeemedAmount,
      egiftRedemptionType,
      balancePayable,
    } = cart.totals;

    if (hasValue(egiftRedeemedAmount)
      && hasValue(egiftRedemptionType)
      && balancePayable <= 0) {
      return true;
    }
  }

  return false;
};

/**
 * Return card number from eGift top-up item options.
 *
 * @todo update the option key for card number.
 */
export const getCardNumberForTopUpItem = (egiftOptions) => (
  (typeof egiftOptions.hps_card_number !== 'undefined')
    ? egiftOptions.hps_card_number
    : ''
);

/**
 * Checks if the response is valid and successful.
 *
 * @param {object} response
 *   The response object.
 *
 * @return {boolean}
 *   True if response is valid and successful else false.
 */
export const isValidResponse = (response) => hasValue(response.data)
  && hasValue(response.data.response_type)
  && response.data.response_type
  && response.status === 200;

/**
 * Checks if the response is invalid with 200 status.
 *
 * @param {object} response
 *   The response object.
 *
 * @return {boolean}
 *   True if response is invalid with 200 status else false.
 */
export const isValidResponseWithFalseResult = (response) => hasValue(response.data)
  && hasValue(response.data.response_type)
  && !response.data.response_type
  && response.status === 200;
