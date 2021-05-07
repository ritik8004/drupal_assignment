import { doRequest, getbazaarVoiceSettings } from './api/request';
import { getStorageInfo, setStorageInfo } from './storage';

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

export const setCurrentUserUasToken = (userId) => {
  const userStorage = getStorageInfo('bvuser') !== null
    ? getStorageInfo('bvuser') : [];
  const requestUrl = '/get-uas-token';
  const request = doRequest(requestUrl);
  let currentUserObj = null;
  // Initliaze user object for anonmymous user.
  if (userId === 0) {
    currentUserObj = {
      userId,
    };
    userStorage.push(currentUserObj);
    setStorageInfo(JSON.stringify(userStorage), 'bvuser');
  } else if (request instanceof Promise) {
    return request
      .then((result) => {
        if (result.status === 200) {
          currentUserObj = {
            userId,
            uasToken: result.data,
          };
          if (currentUserObj !== null) {
            userStorage.push(currentUserObj);
            setStorageInfo(JSON.stringify(userStorage), 'bvuser');
            return userStorage;
          }
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
  let user = null;
  let currentUserObj;
  if (userStorage.length > 0) {
    currentUserObj = userStorage.find((userObj) => userObj.userId === userId);
  }
  user = currentUserObj !== undefined ? currentUserObj : setCurrentUserUasToken(userId);
  return user;
};

export default {
  getCurrentUserEmail,
  getCurrentUserStorage,
  setCurrentUserUasToken,
};
