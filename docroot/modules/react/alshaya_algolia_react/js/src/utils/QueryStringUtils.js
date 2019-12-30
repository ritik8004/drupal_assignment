import qs from 'qs';
import { createBrowserHistory } from 'history';
import { toggleSearchResultsContainer, showLoader } from './SearchUtility';

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

function redirectToOtherLang(queryValue, inputTag) {
  if (queryValue.length === 0) {
    toggleSearchResultsContainer(queryValue);
    return;
  }

  const redirectlang = drupalSettings.path.currentLanguage === 'ar' ? 'en' : 'ar';
  let arabicText = /[\u0600-\u06FF\u0750-\u077F]/.test(queryValue);
  let englishText = /[A-Za-z\s]/.test(queryValue);
  if (drupalSettings.path.currentLanguage === 'en' && arabicText) {
    redirectToUrl(queryValue, 'en', redirectlang, inputTag);

  }
  else if (drupalSettings.path.currentLanguage === 'ar' && englishText) {
    redirectToUrl(queryValue, 'ar', redirectlang, inputTag);
  }
}

function redirectToUrl(queryValue, currentLang, redirectlang, inputTag) {
  // Disable input tag while redirecting to other language;
  if (inputTag !== null && typeof inputTag !== 'undefined') {
    inputTag.disabled = true;
  }

  showLoader();
  localStorage.setItem('algoliaLangRedirect', 1);
  window.location.hash = "query=" + queryValue;
  window.location.pathname = window.location.pathname.replace(currentLang, redirectlang);
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
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues
}
