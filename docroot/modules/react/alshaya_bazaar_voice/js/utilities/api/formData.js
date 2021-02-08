import Axios from 'axios';

export default function formData(apiUri) {
  // const apiUri = '/bv-form-config';
  // const params = `&langcode=en`;
  const url = `${apiUri}?langcode=en`;
  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}
