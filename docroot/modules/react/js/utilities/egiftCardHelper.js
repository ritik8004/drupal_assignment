import { callMagentoApi } from './requestHelper';
import { isUserAuthenticated } from './helper';
import logger from './logger';
import { isEgiftCardEnabled } from './util';

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

/**
 * Checks if Topup is in progress.
 *
 * @returns {object|null}
 *   Returns topup quote object or null.
 */
export const getTopUpQuote = () => {
  // Return null if egift card is not enabled.
  if (!isEgiftCardEnabled()) {
    return null;
  }

  return Drupal.getItemFromLocalStorage('topupQuote');
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

    case 'eGiftRemoveRedemption':
      endpoint = isUserAuthenticated()
        ? '/V1/egiftcard/remove-redemption'
        : '/V1/guest-carts/remove-redemption';
      break;

    case 'eGiftUpdateAmount':
      // Check if Topup is in progress then get topup quoteid and use guest
      // endpoint to perform topup.
      endpoint = isUserAuthenticated() && getTopUpQuote() == null
        ? '/V1/egiftcard/mine/update-redemption-amount'
        : '/V1/egiftcard/guest-carts/update-redemption-amount';
      break;

    case 'eGiftUnlinkCard':
      endpoint = '/V1/egiftcard/unlinkcard';
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
 * @param {boolean} bearerToken
 *   The bearerToken flag.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const callEgiftApi = (action, method, postData, params = {}, bearerToken = true) => {
  const endpoint = getApiEndpoint(action, params);
  return callMagentoApi(endpoint, method, postData, bearerToken);
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
