/**
 * File contains helper method to deal with local storage
 * related get, set, remove.
 */

// Global variable to keep algolia search query.
window.algoliaSearchQuery = '';

function setSearchQuery(queryValue) {
  window.algoliaSearchQuery = queryValue;
}

function getSearchQuery() {
  return window.algoliaSearchQuery;
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
  // Do nothing if user trying to use cmd/ctrl + click OR
  // cmd/ctrl + shift + click.
  if (event.target.tagName.toLowerCase() === 'button'
    || event.metaKey
    || event.shiftKey
    || event.ctrlKey
    || event.altKey
  ) {
    return;
  }

  const articleNode = event.target.closest('.node--view-mode-search-result');

  // Product list container, use this to determine the selected grid view.
  const productList = document.querySelector('.c-products-list');

  // This happens when we display the Add To Bag configurable drawer. The drawer
  // component is outside it's parent article in the DOM so we get an error.
  if (articleNode === null) {
    return;
  }

  const sortByOption = document.getElementById('sort_by').querySelector('.active-item');
  const sortByIndex = sortByOption.getAttribute('data-sort');

  const storageDetails = {
    sku: articleNode.getAttribute('data-sku'),
    page: Drupal.algoliaGetActualPageNumber(),
    sort: sortByIndex,
  };

  // Add grid type property only if productList is available
  // so that default column grid can be used when grid_type
  // is 'undefined'.
  if (productList !== null) {
    storageDetails.grid_type = productList.classList.contains('product-large')
      ? 'large'
      : 'small';
  }

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
  getSearchQuery,
  setLangRedirect,
  removeLangRedirect,
  getLangRedirect,
  setClickedItem,
  storeClickedItem,
};
