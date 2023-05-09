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
 * Checks if the topup quote is expired or not.
 *
 * @returns {boolean}
 *   Returns true if topup quote is expired else false.
 */
export const isTopupQuoteExpired = (topupQuoteExpirationTime) => {
  // Get the topup quote and check if it's expired.
  const topupQuote = Drupal.getItemFromLocalStorage('topupQuote');
  if (topupQuote !== null) {
    const currentTime = new Date().getTime();
    // Calculate the expiration time.
    const expireTime = new Date(topupQuote.created);
    const minutes = expireTime.getMinutes();
    expireTime.setMinutes(minutes + topupQuoteExpirationTime);
    // Remove the topup quote from local storage if expired.
    if (currentTime > expireTime.getTime()) {
      return true;
    }
  }
  return false;
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
  // Check if topup quote is expired, if 'YES' then remove it from local
  // storage.
  const { topupQuoteExpirationTime } = drupalSettings.egiftCard;
  if (topupQuoteExpirationTime > 0 && isTopupQuoteExpired(topupQuoteExpirationTime)) {
    Drupal.removeItemFromLocalStorage('topupQuote');
  }

  return Drupal.getItemFromLocalStorage('topupQuote');
};

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 *
 * @returns {string}
 *   The api endpoint.
 */
export const getApiEndpoint = (action) => {
  let endpoint = '';
  switch (action) {
    case 'eGiftGetBalance':
      endpoint = '/V1/egiftcard/getBalance'; // endpoint to check egift card balance.
      break;

    case 'eGiftRedemption':
      endpoint = '/V1/egiftcard/transact'; // endpoint to do egift card Redemption.
      break;

    case 'eGiftHpsCustomerData':
      endpoint = '/V1/customers/hpsCustomerData'; // endpoint to get egift card details of logged in user.
      break;

    case 'eGiftLinkCard':
      endpoint = '/V1/egiftcard/link'; // endpoint to link egift card with user account.
      break;

    case 'eGiftRemoveRedemption':
      endpoint = isUserAuthenticated()
        ? '/V1/egiftcard/remove-redemption'
        : '/V1/guest-carts/remove-redemption'; // endpoint to remove egift redemption amount.
      break;

    case 'eGiftUpdateAmount':
      // Check if Topup is in progress then get topup quoteid and use guest
      // endpoint to perform topup.
      endpoint = isUserAuthenticated() && getTopUpQuote() == null
        ? '/V1/egiftcard/mine/update-redemption-amount'
        : '/V1/egiftcard/guest-carts/update-redemption-amount'; // endpoint to update egift redemption amount.
      break;

    case 'eGiftUnlinkCard':
      endpoint = '/V1/egiftcard/unlinkcard'; // endpoint to unlink egift card from user account.
      break;

    case 'eGiftProductSearch':
      endpoint = '/V1/products'; // endpoint to get details of egift card or egift topup card.
      break;

    case 'eGiftTopup':
      endpoint = '/V1/egiftcard/topup'; // endpoint to topup a egift card.
      break;

    case 'eGiftCardList':
      endpoint = '/V1/customers/hpsCustomerData'; // endpoint to get the list of linked egift card of a user.
      break;

    case 'unlinkedEiftCardList':
      endpoint = '/V1/egiftcard/mine/associated-with-email'; // endpoint to get the list of unlinked egift cards of a user.
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
 * @param {boolean} bearerToken
 *   The bearerToken flag.
 *
 * @returns {object}
 *   Returns the promise object.
 */
export const callEgiftApi = (action, method, postData, bearerToken = true) => {
  const endpoint = getApiEndpoint(action);
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

/**
 * Allow user to enter only numbers.
 */
export const allowWholeNumbers = (e) => {
  const element = e.target;
  element.value = element.value.replace(/[^\p{N}]/gu, '');
  element.value = element.value.substr(0, 16);
};
