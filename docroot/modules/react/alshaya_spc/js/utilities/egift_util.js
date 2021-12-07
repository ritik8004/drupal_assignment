import React from 'react';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import logger from '../../../js/utilities/logger';

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
 * @param {object} param
 *   The object containing param info.
 *
 * @returns {object}
 *   The response object.
 */
export const callEgiftApi = (action, method, postData, param = {}) => {
  const endpoint = getApiEndpoint(action, param);
  return callMagentoApi(endpoint, method, postData);
};

/*
 * Provides users card number if it is linked.
 */
export const getUserLinkedCardNumber = () => {
  // Return if user is not authenticated.
  if (!isUserAuthenticated()) {
    return { 'card_available': false };
  }

  const params = { email: drupalSettings.userDetails.userEmailID };
  if (params.email) {
    const endpoint = getApiEndpoint('eGiftHpsSearch', params);
    if (endpoint) {
      // Invoke magento API to check if any egift card is already associated
      // with the user account.
      const response = callMagentoApi(endpoint, 'GET');
      if (response instanceof Promise) {
        response.then((result) => {
          if (result.data !== 'undefined'
            && result.error === 'undefined') {
            return { 'card_available': true, 'card_number': result.data.card_number};
          }
          // Handle error response.
          if (result.error) {
            logger.error('Error while calling the egift HPS Search. EmailId: @emailId. Response: @response', {
              '@emailId': params.email,
              '@response': result.data,
            });
          }
          return { 'card_available': false };
        });
      }
    }
  }
  return { 'card_available': false };
};
