import Axios from 'axios';
import _invert from 'lodash/invert';

function getStorageKey(facetName) {
  return `facets:${facetName}:${drupalSettings.path.currentLanguage}`;
}

export function setFacetStorage(facetName, data) {
  if (!data) return null;
  const storageKey = getStorageKey(facetName);
  // Adding data to local storage with expiry time.
  Drupal.addItemInLocalStorage(
    storageKey,
    data,
    parseInt(drupalSettings.algoliaSearch.local_storage_expire, 10) * 60,
  );
  return storageKey;
}

export function removeFacetStorage(facetName) {
  const storageKey = getStorageKey(facetName);
  Drupal.removeItemFromLocalStorage(storageKey);
}

export function getFacetStorage(facetName, inverted = false) {
  const storageKey = getStorageKey(facetName);

  const storageItemArray = Drupal.getItemFromLocalStorage(storageKey);
  if (!storageItemArray) {
    return null;
  }

  return inverted ? _invert(storageItemArray) : storageItemArray;
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

  const requestUri = Drupal.url(`facets-aliases/${facetName}?cacheable=1`);
  if (!apiReqeustInQueue[facetName]) {
    apiReqeustInQueue[facetName] = true;
    Axios.get(requestUri).then((response) => {
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
    requestUrls[facetKey] = Axios.get(Drupal.url(`facets-aliases/${facetKey}?cacheable=1`));
  });
  // fetch data from a url endpoint
  try {
    const response = await Axios.all(Object.values(requestUrls));
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
