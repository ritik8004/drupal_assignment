export {
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues,
  searchStateHasFilter,
} from './QueryStringUtils';

export {
  getPriceRangeLabel,
  calculateDiscount,
  formatPrice,
} from './PriceHelper';

export {
  contentDiv,
  createSearchResultDiv,
  toggleSearchResultsContainer,
  toggleSortByFilter,
  showLoader,
  removeLoader,
  toggleBlockCategoryFilter,
  updatePredictiveSearchContainer,
} from './SearchUtility';

export {
  getFilters,
  hasCategoryFilter,
  getSortedItems,
  hasSuperCategoryFilter,
  facetFieldAlias,
  customQueryRedirect,
} from './FilterUtils';

export {
  setSearchQuery,
  getSearchQuery,
  setLangRedirect,
  removeLangRedirect,
  getLangRedirect,
  setClickedItem,
  storeClickedItem,
} from './localStorage';

export {
  getSuperCategory,
  getSuperCategoryOptionalFilter,
} from './SuperCategoryUtility';
