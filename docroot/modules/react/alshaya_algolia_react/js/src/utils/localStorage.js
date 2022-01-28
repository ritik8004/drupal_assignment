/**
 * File contains helper method to deal with local storage
 * related get, set, remove.
 */

function removeSearchQuery() {
  Drupal.removeItemFromLocalStorage('algolia_search_query');
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
  Drupal.addItemInLocalStorage('algolia_search_query', queryValue);
}

function getSearchQuery() {
  return Drupal.getItemFromLocalStorage('algolia_search_query');
}

function setLangRedirect(queryValue) {
  Drupal.addItemInLocalStorage('algoliaLangRedirect', queryValue);
}

function removeLangRedirect() {
  Drupal.removeItemFromLocalStorage('algoliaLangRedirect');
}

function getLangRedirect() {
  return Drupal.getItemFromLocalStorage('algoliaLangRedirect');
}

function setClickedItem(storageDetails) {
  Drupal.addItemInLocalStorage(`search:${window.location.hash}`, storageDetails);
}

function storeClickedItem(event, pageType) {
  // Do nothing for buttons inside our markup, for example in slick-dots.
  // Do nothing if user trying to use cmd + click.
  if (event.target.tagName.toLowerCase() === 'button' || event.metaKey) {
    return;
  }

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
    Drupal.addItemInLocalStorage(
      `${pageType}:${window.location.pathname}`,
      storageDetails,
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
