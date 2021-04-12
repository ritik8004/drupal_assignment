import Cookies from 'js-cookie';
import { doRequest, getbazaarVoiceSettings } from './api/request';

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

/**
 * Get username of current user.
 *
 * @returns {userName}
 */
export const getCurrentUserName = () => {
  const userName = bazaarVoiceSettings.reviews.user.user_name;
  return userName;
};

export const setSessionCookie = (key, value) => {
  Cookies.remove(key);
  Cookies.set(key, value, { expires: bazaarVoiceSettings.reviews.bazaar_voice.max_age });
};

/**
 * Get UAS Token of current user.
 *
 * @returns {uasToken}
 */
export const getSessionCookie = (key) => {
  let sessionCookie = Cookies.get(key);

  if (sessionCookie === undefined) {
    const currentUserKey = `uas_token_${bazaarVoiceSettings.reviews.user.user_id}`;
    if (key === currentUserKey) {
      const requestUrl = '/get-uas-token';
      const request = doRequest(requestUrl);

      if (request instanceof Promise) {
        request.then((result) => {
          if (result.status === 200) {
            setSessionCookie(key, result.data);
            sessionCookie = Cookies.get(key);
          } else {
            Drupal.logJavascriptError('user-session', result.error);
          }
        });
      }
    } else {
      return null;
    }
  }

  return sessionCookie;
};

export const deleteSessionCookie = (keys) => {
  keys.forEach((item) => {
    Cookies.remove(item);
  });
};

export default {
  getCurrentUserEmail,
  setSessionCookie,
  getSessionCookie,
  deleteSessionCookie,
};
