import React from 'react';
import {
  allowWholeNumbers,
  callEgiftApi,
  getTopUpQuote,
} from '../../../js/utilities/egiftCardHelper';
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

  const checkCardNumber = (e) => {
    const element = e.target;
    if (element.name === 'egift_card_number') {
      allowWholeNumbers(e);
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
        <div className={`egift-type-${type} spc-type-textfield ${className}-wrapper`}>
          <input
            type={type}
            name={`egift_${name}`}
            id={`egift_${name}`}
            className={`${className} ${focusClass}`}
            defaultValue={value}
            disabled={disabled}
            onBlur={(e) => handleEvent(e)}
            onInput={(e) => checkCardNumber(e)}
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
export const updatePriceSummaryBlock = (refreshCart = null) => {
  // Fetch the fresh cart data and update the summary block.
  const cartData = window.commerceBackend.getCart(true);
  if (cartData instanceof Promise) {
    cartData.then((data) => {
      if (data.status === 200
        && data.data !== undefined
        && data.data.error === undefined) {
        // Update Egift card line item.
        dispatchCustomEvent('updateTotalsInCart', { totals: data.data.totals });
        const formatedCart = window.commerceBackend.getCartForCheckout();
        if (formatedCart instanceof Promise) {
          formatedCart.then((cart) => {
            // Validate if response was successful or failure.
            if (hasValue(cart) && hasValue(cart.data)) {
              // Refresh the cart in checkout and check if refresh function is
              // available.
              if (refreshCart) {
                refreshCart({ cart: cart.data });
              } else {
                // Calling refresh cart event so that cart components
                // are refreshed.
                dispatchCustomEvent('refreshCart', {
                  data: () => cart.data,
                });
              }
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
 * Checks if cart contains any virtual product.
 *
 * @param {object} cart
 *   The cart object.
 * @returns {boolean}
 *   Returns true if cart contains any virtual product else false.
 */
export const cartContainsAnyVirtualProduct = (cart) => {
  // A flag to keep track of if there are any virtual products in cart.
  let virtualProductInCart = false;
  Object.values(cart.items).forEach((item) => {
    // Return if we have already encounted a single virtual product.
    if (virtualProductInCart) {
      return;
    }
    // If a single virtual product encounted then mark flag as true.
    if (cartItemIsVirtual(item)) {
      virtualProductInCart = true;
    }
  });

  return virtualProductInCart;
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
      totalBalancePayable,
      base_grand_total: baseGrandTotal,
    } = cart.totals;

    if (hasValue(egiftRedeemedAmount)
      && hasValue(egiftRedemptionType)
      && totalBalancePayable <= 0
      && baseGrandTotal === egiftRedeemedAmount) {
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
  && Object.prototype.hasOwnProperty.call(response.data, 'response_type')
  && !response.data.response_type
  && response.status === 200;

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
  // As we are using guest edit amount redemption in case of Topup, we will not
  // use bearerToken.
  const useBearerToken = (getTopUpQuote() === null);

  // Invoke the redemption API to update the redeem amount.
  const response = await callEgiftApi('eGiftUpdateAmount', 'POST', postData, {}, useBearerToken);
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
 * Removes redemption if it's applied.
 *
 * @param {object} cartData
 *   The cart object.
 */
export const removeEgiftRedemption = async (cartData) => {
  let quoteId = cartData.cart_id;
  // Default result object.
  let result = {
    error: false,
    message: '',
  };
  // Check if topup is applicable.
  const topUpQuote = getTopUpQuote();
  if (topUpQuote) {
    quoteId = topUpQuote.maskedQuoteId;
  }
  let postData = {
    redemptionRequest: {
      mask_quote_id: quoteId,
    },
  };
  // Change payload if authenticated user.
  if (isUserAuthenticated()) {
    postData = {
      redemptionRequest: {
        quote_id: cartData.cart_id_int,
      },
    };
  }

  // Invoke the redemption API.
  const response = await callEgiftApi('eGiftRemoveRedemption', 'POST', postData);
  if (isValidResponse(response)) {
    // Remove loader once the data response is available.
    removeFullScreenLoader();
  } else if (isValidResponseWithFalseResult(response)) {
    result = {
      error: true,
      message: response.data.response_message,
    };
    // Log error in datadog.
    logger.error('Error Response in remove eGiftRedemption. Action: @action Response: @response', {
      '@action': 'remove_redemption',
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
    logger.error('Error Response in remove eGiftRedemption. Action: @action Response: @response', {
      '@action': 'remove_redemption',
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

/**
 * Checks and returns the applicable cart total for egift.
 *
 * @param {object} cart
 *   The cart object.
 *
 * @returns {string}
 *   Returns the applicable cart total for egift.
 */
export const getEgiftCartTotal = (cart) => {
  // Get the cart total and calculate the amount based on balance payable.
  const {
    base_grand_total: baseGrandTotal,
    balancePayable,
    egiftRedeemedAmount,
  } = cart.totals;

  let cartTotal = baseGrandTotal;
  // The cart total for egift should be less than the redemption amount and
  // the pending balance.
  if (balancePayable >= 0
    && egiftRedeemedAmount >= 0
    && (balancePayable + egiftRedeemedAmount) < cartTotal) {
    cartTotal = balancePayable + egiftRedeemedAmount;
  }

  return cartTotal;
};

/**
 * Checks and remove the redemption if exists.
 *
 * @param {object} cart
 *   The cart object.
 */
export const removeRedemptionOnCartUpdate = async (cart) => {
  // Cart response validation is handled in seperate function.
  // If cart.totals present then proceed, else return.
  if (!hasValue(cart.totals)) {
    return;
  }
  // Remove Redemption if any applied in the cart as we have updated the cart
  // items.
  if (isEgiftCardEnabled() && isEgiftRedemptionDone(cart, cart.totals.egiftRedemptionType)) {
    // Remove Redemption as we are updating the cart items.
    showFullScreenLoader();
    const removeRedemption = await removeEgiftRedemption(cart);
    if (removeRedemption.error) {
      // Log the error if redemption is not removed.
      logger.error('Remove redemption failed, @cart', {
        '@cartId': cart,
      });
      // Remove redemption when error occured.
      removeFullScreenLoader();
      return;
    }

    // Update the total in cart summary block.
    updatePriceSummaryBlock();
  }
};

/**
 * Check if top-up quote and get the settings for allow saved credit card.
 *
 * @returns {boolean}
 *   True or false setting.
 */
export const allowSavedCcForTopUp = () => {
  // By default allow saved credit cards.
  let allowSavedCard = true;
  const topUpQuote = getTopUpQuote();
  if (hasValue(topUpQuote)) {
    // If top-up quote then get from settings.
    allowSavedCard = drupalSettings.egiftCard.allowSavedCreditCardForTopup;
  }
  return allowSavedCard;
};
