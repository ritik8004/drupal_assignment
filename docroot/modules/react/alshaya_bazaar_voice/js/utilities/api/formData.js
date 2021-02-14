import Axios from 'axios';

export default function getFormConfig(apiUri) {
  // const apiUri = '/bv-form-config';
  // const params = `&langcode=en`;
  const url = `${apiUri}?langcode=en`;
  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}

function postFile(apiUri, data) {
  return Axios.post(apiUri, data)
    .then((response) => response)
    .catch((error) => error);
}

export {
  getFormConfig,
  postFile,
};
