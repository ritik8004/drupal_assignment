import { doRequest, getUserDetails, fetchAPIData } from './api/request';
import { setStorageInfo, getStorageInfo, removeStorageInfo } from './storage';
import { convertHex2aString } from './write_review_util';

export const getUasToken = (productId) => {
  let requestUrl = 'get-uas-token';
  if (productId !== undefined) {
    requestUrl += `?product=${productId}`;
  }
  const request = doRequest(Drupal.url(requestUrl));
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

export const getReviewStats = (bazaarVoiceSettings) => {
  // Get review data from BazaarVoice based on available parameters.
  const apiUri = '/data/reviews.json';
  const params = `&filter=productid:${bazaarVoiceSettings.productid}&filter=contentlocale:${bazaarVoiceSettings.reviews.bazaar_voice.content_locale}&Include=Products,Comments&Stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}&FilteredStats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}`;
  const apiData = fetchAPIData(apiUri, params);
  if (apiData instanceof Promise) {
    return apiData
      .then((result) => {
        if (!Drupal.hasValue(result.error) && Drupal.hasValue(result.data)) {
          return result.data;
        }

        Drupal.logJavascriptError('review-stats-summary', result.error);
        return null;
      })
      .catch((error) => {
        Drupal.logJavascriptError('review-stats-summary', error);
        return error;
      });
  }
  return null;
};

/**
 * Validate to open writa a review form on page load.
 *
 * @returns {boolean}
 */
export const isOpenWriteReviewForm = (productId) => getUserDetails(productId)
  .then((userDetails) => {
    const query = new URLSearchParams(document.referrer);
    const openPopup = query.get('openPopup');
    if (userDetails.user !== undefined
      && userDetails.user.userId > 0
      && getStorageInfo('openPopup')
      && openPopup !== null
      && userDetails.productReview !== undefined
      && userDetails.productReview === null) {
      return true;
    }
    const path = decodeURIComponent(window.location.search);
    const params = new URLSearchParams(path);
    if ((params.get('messageType') === 'PIE' || params.get('messageType') === 'PIE_FOLLOWUP') && params.get('userToken') !== null) {
      return true;
    }
    return false;
  });

export const getEmailFromTokenParams = (params) => {
  let emailAddress = '';
  const userTokenRaw = convertHex2aString(params.get('userToken'));
  const tokenParamsArr = userTokenRaw.split('&');
  for (let i = 0; i < tokenParamsArr.length; i++) {
    const parameterValue = tokenParamsArr[i].split('=');
    if (decodeURIComponent(parameterValue[0]).toLowerCase() === 'emailaddress') {
      emailAddress = decodeURIComponent(parameterValue[1]);
      return emailAddress;
    }
  }
  return emailAddress;
};

export const createUserStorage = (userId, email, productId) => {
  // Clear user storage for anonmymous user.
  if (userId === 0) {
    removeStorageInfo(`bvuser_${userId}`);
  }
  const userStorage = getStorageInfo(`bvuser_${userId}`);
  const path = decodeURIComponent(window.location.search);
  const params = new URLSearchParams(path);
  // Set uas token if user not found in storage.
  if (userStorage === null) {
    let currentUserObj = null;
    // Initliaze user object for anonmymous user.
    if (userId === 0) {
      currentUserObj = {
        id: userId,
        uasToken: params.get('userToken'),
        email: params.get('userToken') !== null ? getEmailFromTokenParams(params) : '',
      };
      setStorageInfo(currentUserObj, `bvuser_${userId}`);
    } else if ((params.get('messageType') === 'PIE' || params.get('messageType') === 'PIE_FOLLOWUP') && params.get('userToken') !== null) {
      currentUserObj = {
        id: userId,
        uasToken: params.get('userToken'),
        email,
      };
      setStorageInfo(currentUserObj, `bvuser_${userId}`);
    } else {
      getUasToken(productId).then((uasTokenValue) => {
        if (uasTokenValue !== null) {
          currentUserObj = {
            id: userId,
            uasToken: uasTokenValue,
            email,
          };
          setStorageInfo(currentUserObj, `bvuser_${userId}`);
        }
      });
    }
  } else if (params.get('userToken') !== null) {
    userStorage.uasToken = params.get('userToken');
    setStorageInfo(userStorage, `bvuser_${userId}`);
  } else {
    getUasToken(productId).then((uasTokenValue) => {
      if (uasTokenValue !== null) {
        userStorage.uasToken = uasTokenValue;
        setStorageInfo(userStorage, `bvuser_${userId}`);
      }
    });
  }
};

export default {
  getUasToken,
  isOpenWriteReviewForm,
  createUserStorage,
  getReviewStats,
  getEmailFromTokenParams,
};
