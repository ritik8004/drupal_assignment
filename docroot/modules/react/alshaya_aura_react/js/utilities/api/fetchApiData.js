import Axios from 'axios';
import { i18nMiddleWareUrl } from '../../../../alshaya_spc/js/aura-loyalty/components/utilities/helper';
import i18nMiddleWareUrlWithQueryParam from '../../../../alshaya_spc/js/utilities/i18n_url_query_param';

function getAPIData(apiUrl) {
  const url = i18nMiddleWareUrlWithQueryParam(apiUrl);

  return Axios.get(url)
    .then((response) => response)
    .catch((error) => error);
}

function postAPIData(apiUrl, data) {
  const url = i18nMiddleWareUrl(apiUrl);

  return Axios.post(url, data)
    .then((response) => response)
    .catch((error) => error);
}

export {
  getAPIData,
  postAPIData,
};
