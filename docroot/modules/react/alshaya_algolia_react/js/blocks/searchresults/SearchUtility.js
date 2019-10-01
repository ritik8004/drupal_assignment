var contentDiv = document.getElementById('block-content');
// Create Search result div wrapper to render results.
var searchResultDiv = document.createElement('div');

searchResultDiv.id = 'alshaya-algolia-search';
searchResultDiv.className = 'c-plp c-plp-only l-one--w lhn-without-sidebar';
searchResultDiv.style.display = 'none';
contentDiv.parentNode.insertBefore( searchResultDiv, contentDiv.nextSibling );

function showSearchResultContainer() {
  contentDiv.style.display = 'none';
  searchResultDiv.style.display = 'block';
}

function hideSearchResultContainer() {
  contentDiv.style.display = 'block';
  searchResultDiv.style.display = 'none';
}

export {contentDiv, searchResultDiv, showSearchResultContainer, hideSearchResultContainer};
