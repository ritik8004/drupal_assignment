import Axios from 'axios';

const fetchAPIData = (apiUrl) => {
  const url = window.drupalSettings.alshaya_appointment.middleware_url + apiUrl;

  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
};

export default fetchAPIData;
