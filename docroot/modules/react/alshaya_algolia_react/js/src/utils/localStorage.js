/**
 * File contains helper method to deal with local storage
 * related get, set, remove.
 */

function removeSearchQuery() {
  localStorage.removeItem('algolia_search_query');
}

/**
 * Remove search query from local storage to not render search results
 * block when user redirects to another page.
 */
window.onbeforeunload = () => {
  removeSearchQuery();
};

/**
 * Remove search query from local storage to not render search results when
 * no #query or #refinement in url.
 */
window.addEventListener('DOMContentLoaded', () => {
  const query = window.location.hash;
  if (query.indexOf('#query') < 0 && query.indexOf('#refinementList') < 0) {
    removeSearchQuery();
  }
});

function setSearchQuery(queryValue) {
  localStorage.setItem('algolia_search_query', queryValue);
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

function setClickedItem(storageDetails) {
  localStorage.setItem(window.location.hash, JSON.stringify(storageDetails));
}

function storeClickedItem(event, pageType) {
  const articleNode = event.target.closest('.node--view-mode-search-result');

  // This happens when we display the Add To Bag configurable drawer. The drawer
  // component is outside it's parent article in the DOM so we get an error.
  if (articleNode === null) {
    return;
  }

  const storageDetails = {
    sku: articleNode.getAttribute('data-sku'),
    grid_type: articleNode.classList.contains('product-large') ? 'large' : 'small',
    page: Drupal.algoliaGetActualPageNumber(),
  };

  if (pageType === 'plp') {
    localStorage.setItem(
      `${pageType}:${window.location.pathname}`,
      JSON.stringify(storageDetails),
    );
  } else {
    setClickedItem(storageDetails);
  }
}

export {
  setSearchQuery,
  removeSearchQuery,
  getSearchQuery,
  setLangRedirect,
  removeLangRedirect,
  getLangRedirect,
  setClickedItem,
  storeClickedItem,
};
