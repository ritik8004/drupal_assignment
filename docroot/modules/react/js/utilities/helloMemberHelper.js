import { hasValue } from './conditionsUtility';
import logger from './logger';
import { callMagentoApi } from './requestHelper';


/**
 * Helper function to check if Hello Member is enabled.
 */
export default function isHelloMemberEnabled() {
  let enabled = false;

  if (hasValue(drupalSettings.hello_member)) {
    enabled = drupalSettings.hello_member.enabled;
  }

  return enabled;
}

/**
 * Gets magento api endpoint by user role.
 *
 * @param {string} action
 *   Callname for the API.
 *
 * @returns {string}
 *   The api endpoint.
 */
export const getApiEndpoint = (action, params = {}) => {
  let endpoint = '';
  const endPointParams = params;
  switch (action) {
    case 'helloMemberGetCustomerData':
      endpoint = `/V1/customers/apcCustomerData/${endPointParams.customerId}`; // endpoint to check hello member customer data.
      break;
    case 'helloMemberGetApcTierProgressData':
      endpoint = `/V1/customers/apcTierProgressData/customerId/${endPointParams.customerId}`; // endpoint to check hello member customer data.
      break;
    case 'helloMemberBenefitsList':
      endpoint = '/V2/customers/hellomember/benefitsList'; // endpoint to get hello member benefits.
      break;
    case 'helloMemberGetApcPointsHistory':
      endpoint = '/V1/customers/apcTransactions'; // endpoint to get hello member points history.
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
export const callHelloMemberApi = (action, method, postData, bearerToken = true) => {
  const endpoint = getApiEndpoint(action, postData);
  return callMagentoApi(endpoint, method, postData, bearerToken);
};
