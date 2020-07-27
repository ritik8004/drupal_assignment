import Axios from 'axios';
import { removeFullScreenLoader } from '../appointment-util';

function fetchAPIData(apiUrl) {
  const url = drupalSettings.alshaya_appointment.middleware_url + apiUrl;

  return Axios.get(url)
    .then((response) => {
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: response,
        },
      });
      document.dispatchEvent(event);
      return response;
    })
    .catch((error) => {
      removeFullScreenLoader();
      const event = new CustomEvent('showMessage', {
        bubbles: true,
        detail: {
          data: error,
        },
      });
      document.dispatchEvent(event);
      return error;
    });
}

function postAPICall(apiUrl, data) {
  const url = drupalSettings.alshaya_appointment.middleware_url + apiUrl;

  return Axios.post(url, data)
    .then((response) => response)
    .catch((error) => error);
}

export {
  fetchAPIData,
  postAPICall,
};
