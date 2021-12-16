import React from 'react';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import logger from '../../../js/utilities/logger';
import dispatchCustomEvent from '../../../js/utilities/events';
import { removeFullScreenLoader } from '../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../js/utilities/conditionsUtility';

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
    <p>
      <strong>{egiftHeading}</strong>
    </p>
    <p>{egiftSubHeading}</p>
  </div>
);

/**
 * Provides different form element.
 *
 * @param {*} type
 * @param {*} name
 * @param {*} placeholder
 * @param {*} className
 * @param {*} buttonText
 */
export const egiftFormElement = ({
  type = '',
  name = '',
  placeholder = '',
  className = '',
  buttonText = '',
  value = '',
  disabled = false,
}) => {
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
    default:
      rtnTemplate = (
        <div className={`egift-type-${type}`}>
          <input
            type={type}
            name={`egift_${name}`}
            placeholder={placeholder}
            className={className}
            defaultValue={value}
            disabled={disabled}
          />
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
    case 'eGiftSendOtp':
      endpoint = `/V1/sendemailotp/email/${params.email}`;
      break;

    case 'eGiftVerifyOtp':
      endpoint = `/V1/verifyemailotp/email/${params.email}/otp/${params.otp}`;
      break;

    case 'eGiftGetBalance':
      endpoint = '/V1/egiftcard/getBalance';
      break;

    case 'eGiftRedemption':
      endpoint = '/V1/egiftcard/transact';
      break;

    case 'eGiftHpsSearch':
      endpoint = `/V1/egiftcard/hps-search/email/${params.email}`;
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
 *   The response object.
 */
export const callEgiftApi = (action, method, postData, params) => {
  const endpoint = getApiEndpoint(action, params);
  return callMagentoApi(endpoint, method, postData);
};

/**
 <<<<<<< HEAD
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
 * Checks if cart has only egift card products or other products as well.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @return {boolean}
 *   true if it's contains non virtual product else false.
 */
export const cartContainsOnlyNonVirtualProduct = (cart) => {
  // A flag to keep track of the non-virtual products.
  let isNonVirtual = false;
  Object.values(cart.items).forEach((item) => {
    // Return if we have already marked a non virtual product.
    if (isNonVirtual) {
      return;
    }
    // If there is no product type for the cart item then it's non virtual
    // product.
    if ((hasValue(item.product_type) && item.product_type !== 'virtual')
      || (hasValue(item.isEgiftCard) && !item.isEgiftCard && hasValue(item.product_type))) {
      isNonVirtual = true;
    }
  });

  return isNonVirtual;
};

/**
 * Utility function to check if given payment method is unsupported with egift.
 */
export const isEgiftUnsupportedPaymentMethod = (paymentMethod) => {
  const { notSupportedPaymentMethods } = drupalSettings.egiftCard;

  return paymentMethod in notSupportedPaymentMethods;
};

/**
 * Checks if redemptions is performed or not.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @return {boolean}
 *   true if egift redemption is done by guest else false.
 */
export const isEgiftRedemptionDone = (cart) => {
  if (hasValue(cart.totals)) {
    const { egiftRedeemedAmount, egiftRedemptionType } = cart.totals;

    if (hasValue(egiftRedeemedAmount)
      && hasValue(egiftRedemptionType)) {
      return true;
    }
  }

  return false;
};
