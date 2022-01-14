import React from 'react';
import { callEgiftApi, getTopUpQuote } from '../../../js/utilities/egiftCardHelper';
import logger from '../../../js/utilities/logger';
import dispatchCustomEvent from '../../../js/utilities/events';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../js/utilities/showRemoveFullScreenLoader';
import { hasValue } from '../../../js/utilities/conditionsUtility';
import { isEgiftCardEnabled } from '../../../js/utilities/util';
import { isUserAuthenticated } from '../../../js/utilities/helper';
import { getDefaultErrorMessage } from '../../../js/utilities/error';

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
  placeholder = '',
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
            placeholder={placeholder}
            disabled={disabled}
            step="any"
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
            id={`egift_${name}`}
            className={className}
            defaultValue={value}
            placeholder={placeholder}
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
 * Triggers custom event to update price summary block.
 */
export const updatePriceSummaryBlock = (refreshCart) => {
  const cartData = window.commerceBackend.getCart(true);
  if (cartData instanceof Promise) {
    cartData.then((data) => {
      if (data.status === 200
        && data.data !== undefined
        && data.data.error === undefined) {
        // Update Egift card line item.
        dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
        // Refresh the cart in checkout.
        const formatedCart = window.commerceBackend.getCartForCheckout();
        if (formatedCart instanceof Promise) {
          formatedCart.then((cart) => {
            // Validate if response was successful or failure.
            if (hasValue(cart) && hasValue(cart.data)) {
              refreshCart({ cart: cart.data });
            } else {
              dispatchCustomEvent('spcCheckoutMessageUpdate', {
                type: 'error',
                message: getDefaultErrorMessage(),
              });
            }
            // Remove loader once request is full filled.
            removeFullScreenLoader();
          });
        }
      } else {
        dispatchCustomEvent('spcCheckoutMessageUpdate', {
          type: 'error',
          message: getDefaultErrorMessage(),
        });
        // Remove loader once request is full filled.
        removeFullScreenLoader();
      }
    });
  }
};

/**
 * Checks if cart item is virtual or not.
 *
 * @param {object} item
 *   Cart item object.
 *
 * @returns {boolean}
 *   True if cart item is virtual else false.
 */
export const cartItemIsVirtual = (item) => (hasValue(item.product_type) && item.product_type === 'virtual')
  || (Object.prototype.hasOwnProperty.call(item, 'isEgiftCard') && item.isEgiftCard);

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
    if (!cartItemIsVirtual(item)) {
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
 *   true if egift redemption is done by the given redemption type else false.
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

    if (hasValue(cart.totals.extension_attributes)) {
      // Check if data is available in raw cart object.
      const {
        hps_redeemed_amount: rawEgiftRdeemedAmount,
        hps_redemption_type: rawEgiftRedemptionType,
        balance_payble: rawBalancePayable,
      } = cart.totals.extension_attributes;

      if (hasValue(rawEgiftRdeemedAmount)
        && hasValue(rawEgiftRedemptionType)
        && rawBalancePayable <= 0) {
        return true;
      }
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
  && Object.prototype.hasOwnProperty.call(response.data, 'response_type')
  && !response.data.response_type
  && response.status === 200;

/**
 * Checks if bearer token should be passed.
 *
 * @param {string} action
 *   The action user is performing.
 *
 * @return {boolean}
 *   Return true is required else false.
 */
export const isBearerTokenRequired = (action) => {
  if (isEgiftCardEnabled()
    && (action === 'update billing'
      || action === 'update payment'
      || action === 'place order'
      || action === 'update redeem amount')
    && getTopUpQuote()) {
    return false;
  }

  return true;
};

/**
 * Updates the redeem amount.
 *
 * @param {string} updatedAmount
 *   The updated amount provided by user.
 * @param {object} cart
 *   The cart object.
 * @param {function} refreshCart
 *   The function to refresh the cart.
 *
 * @return {object}
 *   The result object containing the information of API call.
 */
export const updateRedeemAmount = async (updatedAmount, cart, refreshCart) => {
  // Check if user is performing topup, if 'YES' then get the topup masked id.
  const topUpQuote = getTopUpQuote();
  // Prepare the post data.
  let postData = {
    redemptionRequest: {
      amount: updatedAmount,
      mask_quote_id: topUpQuote ? topUpQuote.maskedQuoteId : cart.cart_id,
    },
  };

  // Change the post data if user is authenticated.
  // Added check of topup quote as in case of topup, we use guest update
  // redemption endpoint.
  if (isUserAuthenticated() && topUpQuote === null) {
    postData = {
      redemptionRequest: {
        amount: updatedAmount,
      },
    };
  }
  // Default result object.
  let result = {
    error: false,
    message: '',
  };
  showFullScreenLoader();
  // Invoke the redemption API to update the redeem amount.
  const response = await callEgiftApi('eGiftUpdateAmount', 'POST', postData, isBearerTokenRequired('update redeem amount'));
  if (isValidResponse(response)) {
    // Update the cart total.
    updatePriceSummaryBlock(refreshCart);
    // Update the result object with the required data.
    const {
      redeemed_amount: redeemedAmount,
      balance_payable: balancePayable,
      card_number: cardNumber,
    } = response.data;

    result = {
      error: false,
      redeemedAmount,
      balancePayable,
      cardNumber,
    };
  } else if (isValidResponseWithFalseResult(response)) {
    result = {
      error: true,
      message: response.data.response_message,
    };
    // Log error in datadog.
    logger.error('Error Response in eGiftUpdateAmount. Action: @action Response: @response', {
      '@action': 'update Amount',
      '@response': response.data,
    });
    // Remove loader once the data response is available.
    removeFullScreenLoader();
  } else {
    result = {
      error: true,
      message: getDefaultErrorMessage(),
    };
    // Log error in datadog.
    logger.error('Error Response in eGiftUpdateAmount. Action: @action Response: @response', {
      '@action': 'update Amount',
      '@response': response,
    });
    // Remove loader once the data response is available.
    removeFullScreenLoader();
  }

  return result;
};

/**
 * Checks if user is trying to topup and redeem using same card.
 *
 * @param {object} cart
 *   The cart object.
 * @param {string} cardNumber
 *   The card number using trying to redeem.
 *
 * @returns {boolean}
 *   Returns true if trying to topup and redeem using name card else false.
 */
export const selfCardTopup = (cart, cardNumber) => {
  let selfTopup = false;
  // Check if user is trying to use topup and redeem same card.
  Object.keys(cart.items).forEach((key) => {
    if (hasValue(cart.items[key].topupCardNumber)
      && cardNumber === cart.items[key].topupCardNumber) {
      selfTopup = true;
    }
  });

  return selfTopup;
};
