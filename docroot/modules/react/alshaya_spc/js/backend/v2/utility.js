import Axios from 'axios';
import { getTopUpQuote } from '../../../../js/utilities/egiftCardHelper';
import logger from '../../../../js/utilities/logger';

/**
 * Get user role authenticated or anonymous.
 *
 * @returns {boolean}
 *   True if user is authenticated.
 */
const isUserAuthenticated = () => Boolean(window.drupalSettings.userDetails.customerId);

const removeCartIdFromStorage = () => {
  // Always remove cart_data, we have added this as workaround with
  // to-do at right place.
  Drupal.removeItemFromLocalStorage('cart_data');

  // Remove Add to cart PDP count.
  Drupal.removeItemFromLocalStorage('skus_added_from_pdp');

  if (isUserAuthenticated()) {
    Drupal.addItemInLocalStorage('cart_id', window.authenticatedUserCartId);
    // Remove guest Cart for merge from storage.
    Drupal.removeItemFromLocalStorage('guestCartForMerge');
    return;
  }

  // Remove user cart id if user is not authenticated.
  Drupal.removeItemFromLocalStorage('user_cart_id');

  Drupal.removeItemFromLocalStorage('cart_id');
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
const getApiEndpoint = (action, params = {}) => {
  let endpoint = '';
  let topUpQuote = null;
  // Reassigning params to updated the cart id.
  // Doing this to avoid no-param-reassign eslint issue.
  const endPointParams = params;

  switch (action) {
    case 'associateCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/associate-cart'
        : '';
      break;

    case 'createCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine'
        : '/V1/guest-carts';
      break;

    case 'getCart':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/getCart'
        : `/V1/guest-carts/${endPointParams.cartId}/getCart`;
      break;

    case 'addUpdateItems':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/items'
        : `/V1/guest-carts/${endPointParams.cartId}/items`;
      break;

    case 'removeItems':
      endpoint = isUserAuthenticated()
        ? `/V1/carts/mine/items/${endPointParams.itemId}`
        : `/V1/guest-carts/${endPointParams.cartId}/items/${endPointParams.itemId}`;
      break;

    case 'updateCart':
      // Check if Topup is in progress then get topup quoteid and use guest
      // endpoint to perform topup.
      topUpQuote = getTopUpQuote();
      if (topUpQuote !== null) {
        endPointParams.cartId = topUpQuote.maskedQuoteId;
      }
      // If user is authenticated and trying to topup then we will use guest
      // update cart endpoint.
      endpoint = isUserAuthenticated() && topUpQuote === null
        ? '/V1/carts/mine/updateCart'
        : `/V1/guest-carts/${endPointParams.cartId}/updateCart`;
      break;

    case 'estimateShippingMethods':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/estimate-shipping-methods'
        : `/V1/guest-carts/${endPointParams.cartId}/estimate-shipping-methods`;
      break;

    case 'getPaymentMethods':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/payment-methods'
        : `/V1/guest-carts/${endPointParams.cartId}/payment-methods`;
      break;

    case 'selectedPaymentMethod':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/selected-payment-method'
        : `/V1/guest-carts/${endPointParams.cartId}/selected-payment-method`;
      break;

    case 'placeOrder':
      // Check if Topup is in progress then get topup quoteid and use guest
      // endpoint to perform topup.
      topUpQuote = getTopUpQuote();
      if (topUpQuote !== null) {
        endPointParams.cartId = topUpQuote.maskedQuoteId;
      }
      // If user is authenticated and trying to topup then we will use guest
      // update cart endpoint.
      endpoint = isUserAuthenticated() && topUpQuote === null
        ? '/V1/carts/mine/order'
        : `/V1/guest-carts/${endPointParams.cartId}/order`;
      break;

    case 'getCartStores':
      endpoint = isUserAuthenticated()
        ? `/V1/click-and-collect/stores/cart/mine/lat/${endPointParams.lat}/lon/${endPointParams.lon}`
        : `/V1/click-and-collect/stores/guest-cart/${endPointParams.cartId}/lat/${endPointParams.lat}/lon/${endPointParams.lon}`;
      break;

    case 'getLastOrder':
      endpoint = isUserAuthenticated()
        ? '/V1/customer-order/me/getLastOrder'
        : '';
      break;

    case 'getTabbyAvailableProducts':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/tabby-available-products'
        : `/V1/guest-carts/${endPointParams.cartId}/tabby-available-products`;
      break;

    case 'mergeGuestCart':
      endpoint = isUserAuthenticated()
        ? `/V1/cart-merge/customer-id/${endPointParams.customerId}/active-quote/${endPointParams.activeQuote}/store-id/${endPointParams.storeId}`
        : '';
      break;

    case 'validateOrder':
      endpoint = !isUserAuthenticated()
        ? `/V1/guest-carts/${endPointParams.cartId}/order`
        : '';
      break;

    case 'codMobileVerificationSendOtp':
      endpoint = '/V1/carts/otp/send';
      break;

    case 'codMobileVerificationValidateOtp':
      endpoint = '/V1/carts/otp/verify';
      break;

    case 'getTamaraAvailability':
      endpoint = isUserAuthenticated()
        ? '/V1/carts/mine/tamara-payment-availability'
        : `/V1/guest-carts/${endPointParams.cartId}/tamara-payment-availability`;
      break;

    case 'tokenizedCards':
      endpoint = '/V1/checkoutcomupapi/getTokenList';
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets the ip address of the client.
 *
 * @returns {string}
 *   Thge ip address.
 */
const getIp = () => Axios({ url: 'https://www.cloudflare.com/cdn-cgi/trace' })
  .then((response) => {
    if (typeof response.data === 'undefined' || response.data === '') {
      return '';
    }
    return response.data.trim().split('\n').map((e) => {
      const item = e.split('=');
      return (item[0] === 'ip') ? item[1] : null;
    }).filter((value) => value != null)[0];
  });

/**
 * Check If request is from SocialAuth Popup
 *
 * @returns {boolean}
 *   True request is from socialAuth Popup.
 */
const isRequestFromSocialAuthPopup = () => {
  if (window.name === 'ConnectWithSocialAuth') {
    return true;
  }
  return false;
};

/* eslint-disable import/prefer-default-export */
export {
  getApiEndpoint,
  isUserAuthenticated,
  getIp,
  removeCartIdFromStorage,
  isRequestFromSocialAuthPopup,
};
