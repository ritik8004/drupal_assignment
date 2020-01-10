/**
 * File contains helper method to deal with local storage
 * related get, set, remove.
 */

/**
 * Remove search query from local storage to not render search results
 * block when user redirects to another page.
 */
window.onbeforeunload = function(event) {
  remove_search_query();
};

function set_search_query(queryValue) {
  localStorage.setItem('algolia_search_query', queryValue);
}

function remove_search_query() {
  localStorage.removeItem('algolia_search_query');
}

function get_search_query() {
  return localStorage.getItem('algolia_search_query');
}

function set_lang_redirect(queryValue) {
  localStorage.setItem('algoliaLangRedirect', queryValue);
}

function remove_lang_redirect() {
  localStorage.removeItem('algoliaLangRedirect');
}

function get_lang_redirect() {
  return localStorage.getItem('algoliaLangRedirect');
}

function set_clicked_item(storage_details) {
  localStorage.setItem(window.location.hash, JSON.stringify(storage_details));
}

export {
  set_search_query,
  remove_search_query,
  get_search_query,
  set_lang_redirect,
  remove_lang_redirect,
  get_lang_redirect,
  set_clicked_item
}
