import { hasValue } from './conditionsUtility';
import { getErrorResponse } from './error';
import { isUserAuthenticated } from './helper';
import logger from './logger';
import { callMagentoApi } from './requestHelper';


/**
 * Helper function to check if Hello Member is enabled.
 */
export default function isHelloMemberEnabled() {
  return hasValue(drupalSettings.helloMember) && hasValue(drupalSettings.helloMember.status);
}

/**
 * Helper function to check if aura integration with hello member is enabled.
 */
export const isAuraIntegrationEnabled = () => isHelloMemberEnabled()
  && hasValue(drupalSettings.helloMember.auraIntegrationStatus);

/**
 * Helper function to get aura related config for hello memeber.
 */
export const getAuraFormConfig = () => {
  if (hasValue(drupalSettings.helloMember.auraFormConfig)) {
    return drupalSettings.helloMember.auraFormConfig;
  }
  return null;
};

/**
 * Helper function to get the customer info from user session.
 */
export const getHelloMemberCustomerInfo = () => {
  // Get user details from session.
  const { customerId } = drupalSettings.userDetails;
  const { uid } = drupalSettings.user;

  // Check if we have user in session.
  if (!hasValue(customerId) || uid === 0) {
    logger.error('Error while trying to get customer info. No user available in session. User id: @user_id. Customer id: @customer_id.', {
      '@user_id': uid,
      '@customer_id': customerId,
    });
    return getErrorResponse('No user available in session', 403);
  }

  const params = {
    customerId,
    programCode: 'hello_member',
  };

  return params;
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
export const getApiEndpoint = (action, params = {}, postParams) => {
  let endpoint = '';
  const endPointParams = params;
  switch (action) {
    case 'helloMemberGetCustomerData':
      endpoint = `/V1/customers/apcCustomerData/${endPointParams.customerId}`; // endpoint to check hello member customer data.
      break;
    case 'helloMemberGetTierProgressData':
      endpoint = `/V1/customers/apcTierProgressData/customerId/${endPointParams.customerId}`; // endpoint to check hello member customer data.
      break;
    case 'helloMemberCouponsList':
      endpoint = '/V1/hello-member/customers/coupons'; // endpoint to get hello member coupons list.
      break;
    case 'helloMemberCouponPage':
      endpoint = `/V1/hello-member/customers/coupons/id/${endPointParams.code}`; // endpoint to get hello member coupon details.
      break;
    case 'helloMemberOffersList':
      endpoint = '/V1/hello-member/customers/offers'; // endpoint to get hello member offers list.
      break;
    case 'helloMemberOfferPage':
      endpoint = `/V1/hello-member/customers/offers/code/${endPointParams.code}`; // endpoint to get hello member offer details.
      break;
    case 'helloMemberGetPointsHistory':
      endpoint = '/V1/customers/apcTransactions'; // endpoint to get hello member points history.
      break;
    case 'helloMemberGetDictionaryData':
      endpoint = `/V1/customers/apcDicData/${endPointParams.type}`; // endpoint to get hello member dictonary data.
      break;
    case 'helloMemberGetPointsEarned':
      endpoint = `/V1/apc/${postParams.identifierNo}/sales`; // endpoint to get hello member points earned data.
      break;
    case 'helloMemberSetLoyaltyCard':
      endpoint = '/V1/customers/mine/set-loyalty-card'; // endpoint to set hello member loyalty card details.
      break;
    case 'helloMemberCustomerPhoneSearch':
      endpoint = `/V1/customers/apc-search/phone/${endPointParams.phoneNumber}`; // endpoint to search hello member by phone number.
      break;
    case 'addBonusVouchersToCart':
      endpoint = '/V1/hello-member/carts/mine/bonusVouchers';
      break;
    case 'addMemberOffersToCart':
      endpoint = '/V1/hello-member/carts/mine/memberOffers';
      break;
    case 'getCartData':
      endpoint = '/V1/carts/mine/getCart';
      break;
    case 'unsetLoyaltyCard':
      endpoint = isUserAuthenticated()
        ? '/V1/customers/mine/unset-loyalty-card'
        : '/V1/apc/unset-loyalty-card';
      break;
    case 'getAuraCustomerPoints':
      endpoint = `/V1/guest/apc-points-balance/identifierNo/${endPointParams.identifierNo}`;
      break;
    case 'helloMemberRemoveOffers':
      endpoint = '/V1/hello-member/carts/mine/memberOffers';
      break;
    case 'helloMemberRemovebonusVouchers':
      endpoint = '/V1/hello-member/carts/mine/bonusVouchers';
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  return endpoint;
};

/**
 * Gets hello member data response from magento api endpoint.
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
export const callHelloMemberApi = (action, method, postData, postParams, bearerToken = true) => {
  const endpoint = getApiEndpoint(action, postData, postParams);
  return callMagentoApi(endpoint, method, postData, bearerToken);
};
