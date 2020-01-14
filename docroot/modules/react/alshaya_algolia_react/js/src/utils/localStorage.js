/**
 * File contains helper method to deal with local storage
 * related get, set, remove.
 */

/**
 * Remove search query from local storage to not render search results
 * block when user redirects to another page.
 */
window.onbeforeunload = function(event) {
  removeSearchQuery();
};

window.addEventListener('DOMContentLoaded', (event) => {
  let query = window.location.hash;
  if (query.indexOf('#query') < 0 && query.indexOf('#refinementList') < 0) {
    removeSearchQuery();
  }
});

function setSearchQuery(queryValue) {
  localStorage.setItem('algolia_search_query', queryValue);
}

function removeSearchQuery() {
  localStorage.removeItem('algolia_search_query');
}

function getSearchQuery() {
  return localStorage.getItem('algolia_search_query');
}

function setLangRedirect(queryValue) {
  localStorage.setItem('algoliaLangRedirect', queryValue);
}

function removeLangRedirect() {
  localStorage.removeItem('algoliaLangRedirect');
}

function getLangRedirect() {
  return localStorage.getItem('algoliaLangRedirect');
}

function setClickedItem(storage_details) {
  localStorage.setItem(window.location.hash, JSON.stringify(storage_details));
}

export {
  setSearchQuery,
  removeSearchQuery,
  getSearchQuery,
  setLangRedirect,
  removeLangRedirect,
  getLangRedirect,
  setClickedItem
}
