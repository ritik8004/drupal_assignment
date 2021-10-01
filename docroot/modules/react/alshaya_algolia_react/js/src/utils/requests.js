import axios from 'axios';
import _invert from 'lodash/invert';

function getStorageKey(facetName) {
  return `facets:${facetName}:${drupalSettings.path.currentLanguage}`;
}

export function setFacetStorage(facetName, data) {
  if (!data) return null;
  const storageKey = getStorageKey(facetName);
  // Adding current time to storage.
  const facetInfo = { data, last_update: new Date().getTime() };
  const dataToStore = JSON.stringify(facetInfo);
  localStorage.setItem(storageKey, dataToStore);
  return storageKey;
}

export function removeFacetStorage(facetName) {
  const storageKey = getStorageKey(facetName);
  localStorage.removeItem(storageKey);
}

export function getFacetStorage(facetName, inverted = false) {
  const storageKey = getStorageKey(facetName);

  const storageItem = localStorage.getItem(storageKey);
  if (!storageItem) {
    return null;
  }

  const storageItemArray = JSON.parse(storageItem);
  const storageExpireTime = parseInt(drupalSettings.algoliaSearch.local_storage_expire, 10);
  const expireTime = storageExpireTime * 60 * 1000;
  const currentTime = new Date().getTime();

  // Return null if data is expired and clear localStorage.
  if (((currentTime - storageItemArray.last_update) > expireTime)) {
    removeFacetStorage(storageKey);
    return null;
  }

  return inverted ? _invert(storageItemArray.data) : storageItemArray.data;
}

// Store the api requests that are in queue.
const apiReqeustInQueue = {};

/**
 * Make api request to get aliases for facet values of given
 * facet key. And store it in local storage.
 *
 * @param {*} facetName
 *   The facet key for which we have to make api request.
 */
export function makeFacetAliasApiRequest(facetName) {
  const facetInfo = getFacetStorage(facetName);
  if (facetInfo) return;

  const requestUri = Drupal.url(`facets-aliases/${facetName}`);
  if (!apiReqeustInQueue[facetName]) {
    apiReqeustInQueue[facetName] = true;
    axios.get(requestUri).then((response) => {
      setFacetStorage(facetName, response.data);
    }).catch(() => delete apiReqeustInQueue[facetName]);
  }
}

/**
 * Make async api request for givenlist of facets and return
 * the values.
 *
 * @param {*} apiRequests
 */
export async function asyncFacetValuesRequest(apiRequests, inverted = true) {
  const requestUrls = [];
  apiRequests.forEach((facetKey) => {
    apiReqeustInQueue[facetKey] = true;
    requestUrls[facetKey] = axios.get(Drupal.url(`facets-aliases/${facetKey}`));
  });
  // fetch data from a url endpoint
  try {
    const response = await axios.all(Object.values(requestUrls));
    const data = [];
    Object.keys(requestUrls).forEach((key, index) => {
      if (response[index].data && Object.keys(response[index].data).length > 0) {
        setFacetStorage(key, response[index].data);
        // Invert the data object, so that aliases can be used directly
        // as a key.
        data[key] = inverted ? _invert(response[index].data) : response[index].data;
      }
    });
    return data;
  } catch (error) {
    Drupal.logJavascriptError('selected-facets-api-request', error);
    // appropriately handle the error
  }
  return false;
}
