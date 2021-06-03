import { doRequest, getUserDetails } from './api/request';
import { setStorageInfo, getStorageInfo } from './storage';

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
export const isOpenWriteReviewForm = (productId) => {
  const userDetails = getUserDetails(productId);
  const query = new URLSearchParams(document.referrer);
  const openPopup = query.get('openPopup');
  if (userDetails !== undefined
    && userDetails.id > 0
    && getStorageInfo('openPopup')
    && openPopup !== null
    && userDetails.review === null) {
    return true;
  }
  return false;
};

export const createUserStorage = (userId, email) => {
  const userStorage = getStorageInfo(`bvuser_${userId}`);
  // Set uas token if user not found in storage.
  if (userStorage === null) {
    let currentUserObj = null;
    // Initliaze user object for anonmymous user.
    if (userId === 0) {
      currentUserObj = {
        id: userId,
      };
      setStorageInfo(currentUserObj, `bvuser_${userId}`);
    } else {
      getUasToken().then((uasTokenValue) => {
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
  }
};

export default {
  getUasToken,
  isOpenWriteReviewForm,
  createUserStorage,
};
