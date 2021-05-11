import { doRequest, getbazaarVoiceSettings } from './api/request';
import { fetchAPIData } from './api/apiData';

const bazaarVoiceSettings = getbazaarVoiceSettings();

/**
 * Get email address of current user.
 *
 * @returns {email}
 */
export const getCurrentUserEmail = () => {
  const email = bazaarVoiceSettings.reviews.user.user_email;
  return email;
};

export const getUasToken = () => {
  const requestUrl = '/get-uas-token';
  const request = doRequest(requestUrl);
  if (request instanceof Promise) {
    return request
      .then((result) => {
        if (result.status === 200) {
          return result.data;
        }
        return null;
      })
      .catch((error) => error);
  }
  return null;
};

/**
 * Get reviews posted by user.
 * @param userId
 */
export const getUserReviews = (userId) => {
  const apiUri = '/data/authors.json';
  const params = `&filter=id:${userId}&Include=Reviews`;
  const apiData = fetchAPIData(apiUri, params);
  if (apiData instanceof Promise) {
    return apiData
      .then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.Results.length > 0) {
            return result.data.Includes.Reviews;
          }
        }
        return null;
      })
      .catch((error) => error);
  }
  return null;
};

export default {
  getCurrentUserEmail,
  getUasToken,
  getUserReviews,
};
