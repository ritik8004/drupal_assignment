import Axios from 'axios';

function fetchAPIData(apiUrl) {
  const url = window.drupalSettings.alshaya_appointment.middleware_url + apiUrl;

  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}

function postAPICall(apiUrl, data) {
  const url = window.drupalSettings.alshaya_appointment.middleware_url + apiUrl;

  return Axios.post(url, data)
    .then((response) => response)
    .catch((error) => error);
}

export {
  fetchAPIData,
  postAPICall,
};
