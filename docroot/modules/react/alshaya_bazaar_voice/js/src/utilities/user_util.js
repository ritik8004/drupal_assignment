import { doRequest, getbazaarVoiceSettings } from './api/request';
import { getStorageInfo } from './storage';

const bazaarVoiceSettings = getbazaarVoiceSettings();

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
 * Validate to open writa a review form on page load.
 *
 * @returns {boolean}
 */
export const isOpenWriteReviewForm = () => {
  const query = new URLSearchParams(document.referrer);
  const openPopup = query.get('openPopup');
  if (bazaarVoiceSettings.reviews !== undefined
    && bazaarVoiceSettings.reviews.user.id > 0
    && getStorageInfo('openPopup')
    && openPopup !== null
    && bazaarVoiceSettings.reviews.user.review === null) {
    return true;
  }
  return false;
};

export default {
  getUasToken,
  isOpenWriteReviewForm,
};
