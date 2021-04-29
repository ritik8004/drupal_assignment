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

export const getUserEmailParams = (email, nicknameKey) => {
  let params = '';
  // Delete existing cookies for user info.
  if (getSessionCookie('bv_user_email') !== null && getSessionCookie('bv_user_email') !== email) {
    const cookieValues = ['bv_user_email', nicknameKey, 'bv_user_id'];
    deleteSessionCookie(cookieValues);
  }
  // Set auth paramters for anonymous users.
  if (getCurrentUserEmail() === null && getSessionCookie('bv_user_email') === null) {
    params += `&HostedAuthentication_AuthenticationEmail=${email}&HostedAuthentication_CallbackURL=${bazaarVoiceSettings.reviews.base_url}${bazaarVoiceSettings.reviews.product.url}`;
  }
  return params;
};

export const getUserNicknameParams = (nicknameKey, nickname) => {
  let params = '';
  if (getSessionCookie(nicknameKey) !== nickname) {
    params += `&UserNickname=${nickname}`;
    setSessionCookie(nicknameKey, nickname);
  }
  params += `&User=${getSessionCookie('bv_user_id')}`;
  return params;
};

export default {
  getCurrentUserEmail,
  setSessionCookie,
  getSessionCookie,
  deleteSessionCookie,
  getUserEmailParams,
  getUserNicknameParams,
};
