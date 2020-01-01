import qs from 'qs';
import { createBrowserHistory } from 'history';

const updateAfter = 700;
const history = createBrowserHistory();

function getCurrentSearchQueryString() {
  return qs.parse(window.location.hash.substr(1));
}

function getCurrentSearchQuery() {
  const parsedHash = getCurrentSearchQueryString();
  return parsedHash && parsedHash.query ? parsedHash.query : '';
}

// Push query to browser histroy to ga back and see previous results.
function updateSearchQuery(queryValue) {
  history.push({hash: queryValue});
}

function searchStateToURL(searchState) {
  return searchState.query ? qs.stringify(searchState) : '';
}

function isMobile() {
  return (window.innerWidth < 768);
}

/**
 * Get the storage values.
 *
 * @returns {null}
 */
function getAlgoliaStorageValues() {
  var value = localStorage.getItem(window.location.hash);
  if (typeof value !== 'undefined' && value !== null) {
    return JSON.parse(value);
  }

  return null;
}

export {
  searchStateToURL,
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  isMobile,
  getAlgoliaStorageValues
}
