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
export const getApiEndpoint = (action) => {
  let endpoint = '';
  switch (action) {
    case 'helloMemberGetCustomerData':
      endpoint = '/V2/customers/apcCustomerData'; // endpoint to check hello member customer data.
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
  const endpoint = getApiEndpoint(action);
  return callMagentoApi(endpoint, method, postData, bearerToken);
};
