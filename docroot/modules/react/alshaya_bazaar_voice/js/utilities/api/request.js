import Axios from 'axios';

export function doRequest(url) {
  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}

export function postRequest(url, data) {
  return Axios.post(url, data)
    .then((response) => response)
    .catch((error) => error);
}

export default {
  doRequest,
  postRequest,
};
