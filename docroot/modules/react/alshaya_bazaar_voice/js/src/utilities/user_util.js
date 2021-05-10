import { doRequest, getbazaarVoiceSettings } from './api/request';
import { getStorageInfo, updateStorageInfo } from './storage';
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

export const setCurrentUserStorage = (userId) => {
  const userStorage = getStorageInfo('bvuser') !== null
    ? getStorageInfo('bvuser') : [];
  const requestUrl = '/get-uas-token';
  const request = doRequest(requestUrl);
  let currentUserObj = null;
  // Initliaze user object for anonmymous user.
  if (userId === 0) {
    currentUserObj = {
      id: userId,
    };
    updateStorageInfo('bvuser', currentUserObj, userId);
  } else if (request instanceof Promise) {
    return request
      .then((result) => {
        if (result.status === 200) {
          currentUserObj = {
            id: userId,
            uasToken: result.data,
            email: getCurrentUserEmail(),
          };
          updateStorageInfo('bvuser', currentUserObj, userId);
        }
        return null;
      })
      .catch((error) => error);
  }
  return userStorage;
};

export const getCurrentUserStorage = (userId) => {
  const userStorage = getStorageInfo('bvuser') !== null
    ? getStorageInfo('bvuser') : [];
  if (userStorage.length > 0) {
    const currentUserObj = userStorage.find((userObj) => userObj.id === userId);
    if (currentUserObj) {
      return currentUserObj;
    }
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

/**
 * Update storage data of current user.
 * @param userId
 * @param nickname
 * @param email
 */
export const updateUserStorageData = (userId, nickname, email) => {
  const userObj = {
    id: userId,
    nickname,
  };
  if (email !== undefined) {
    userObj.email = email;
  }
  updateStorageInfo('bvuser', userObj, userId);
};

export default {
  getCurrentUserEmail,
  getCurrentUserStorage,
  setCurrentUserStorage,
  getUserReviews,
  updateUserStorageData,
};
