export {
  searchStateToURL,
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  isMobile,
  getAlgoliaStorageValues
} from './QueryStringUtils';

export {
  getPriceRangeLabel,
  calculateDiscount,
  formatPrice
} from './PriceHelper';

export {
  contentDiv,
  searchResultDiv,
  toggleSearchResultsContainer,
  showLoader,
  removeLoader
} from './SearchUtility';

export {
  getFilters,
  hasCategoryFilter
} from './FilterUtils';
