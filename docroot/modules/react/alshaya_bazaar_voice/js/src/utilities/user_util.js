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

export const setSessionCookie = (uasToken, maxAge) => {
  Cookies.remove('uas_token');
  Cookies.set('uas_token', uasToken, { expires: maxAge });
};

/**
 * Get UAS Token of current user.
 *
 * @returns {uasToken}
 */
export const getSessionCookie = () => {
  const sessionCookie = Cookies.get('uas_token');

  if (sessionCookie === 'undefined') {
    const requestUrl = '/get-uas-token';
    const request = doRequest(requestUrl);

    if (request instanceof Promise) {
      request.then((result) => {
        if (result.status === 200 && result.statusText === 'OK') {
          setSessionCookie(result.data, bazaarVoiceSettings.reviews.bazaar_voice.max_age);
        } else {
          Drupal.logJavascriptError('user-session', result.error);
        }
      });
    }
  } else {
    return sessionCookie;
  }
  return null;
};

export default {
  getCurrentUserEmail,
  getSessionCookie,
};
