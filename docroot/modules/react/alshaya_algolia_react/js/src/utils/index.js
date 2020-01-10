export {
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues,
  searchStateHasFilter
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
  toggleSortByFilter,
  showLoader,
  removeLoader
} from './SearchUtility';

export {
  getFilters,
  hasCategoryFilter
} from './FilterUtils';

export {
  set_search_query,
  remove_search_query,
  get_search_query,
  set_lang_redirect,
  remove_lang_redirect,
  get_lang_redirect,
  set_clicked_item
} from './localStorage';