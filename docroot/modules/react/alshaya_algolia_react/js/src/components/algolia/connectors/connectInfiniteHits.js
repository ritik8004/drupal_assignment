import _defineProperty from '@babel/runtime/helpers/esm/defineProperty';
import _toConsumableArray from '@babel/runtime/helpers/esm/toConsumableArray';
import _objectWithoutProperties from '@babel/runtime/helpers/esm/objectWithoutProperties';
import _reduce from 'lodash/reduce';
import _isPlainObject from 'lodash/isPlainObject';
import { createConnector } from 'react-instantsearch-dom';
import {
  getCurrentRefinementValue,
  refineValue,
  getResults,
  addAbsolutePositions,
  addQueryID,
} from '../../../utils/indexUtils';

function getId() {
  return 'page';
}

function getCurrentRefinement(props, searchState, context) {
  const id = getId();
  const page = 1;
  const currentRefinement = getCurrentRefinementValue(props, searchState, context, id, page);

  if (typeof props.defaultpageRender === 'number') {
    return props.defaultpageRender;
  }

  if (typeof currentRefinement === 'string') {
    return parseInt(currentRefinement, 10);
  }

  return currentRefinement;
}

function diffObject(obj1, obj2) {
  return _reduce(obj1, (result, value, key) => {
    const finalResult = result;
    if (_isPlainObject(value) && (typeof obj2 !== 'undefined' && typeof obj2[key] !== 'undefined')) {
      finalResult[key] = diffObject(value, obj2[key]);
    } else if ((typeof obj2 !== 'undefined' && typeof obj2[key] !== 'undefined') && JSON.stringify(value) !== JSON.stringify(obj2[key])) {
      finalResult[key] = value;
    }
    return finalResult;
  }, {});
}

/**
 * InfiniteHits connector provides the logic to create connected
 * components that will render an continuous list of results retrieved from
 * Algolia. This connector provides a function to load more results.
 * @name connectInfiniteHits
 * @kind connector
 * @providedPropType {array.<object>} hits - the records that matched the search state
 * @providedPropType {boolean} hasMore - indicates if there are more pages to load
 * @providedPropType {function} refine - call to load more results
 */
export default createConnector({
  displayName: 'AlgoliaInfiniteHits',
  getProvidedProps: function getProvidedProps(props, searchState, searchResults) {
    const thisLocal = this;

    const results = getResults(searchResults, this.context);
    this.allResults = this.allResults || [];
    this.prevState = this.prevState || {};

    if (!results) {
      return {
        hits: [],
        hasPrevious: false,
        hasMore: false,
        defaultpageRender: false,
        refine: function refine() {},
        refinePrevious: function refinePrevious() {},
        refineNext: function refineNext() {},
      };
    }

    const page = (this.firstReceivedPage === undefined && typeof props.defaultpageRender === 'number') ? props.defaultpageRender - 1 : results.page;
    const { hits } = results;
    const { hitsPerPage } = results;
    let { nbPages } = results;

    /* Dangling variable _state is coming from an external library here. */
    // eslint-disable-next-line no-underscore-dangle
    let resultsState = results._state;

    resultsState = resultsState === undefined ? {} : resultsState;

    const currentState = _objectWithoutProperties(resultsState, ['page']);

    const hitsWithPositions = addAbsolutePositions(hits, hitsPerPage, page);
    const hitsWithPositionsAndQueryID = addQueryID(hitsWithPositions, results.queryID);

    if (this.firstReceivedPage === undefined && typeof props.defaultpageRender === 'number') {
      this.allResults = _toConsumableArray(hitsWithPositionsAndQueryID);
      // Used Drupal settings to know the pages based on set number of items.
      if (results.nbHits > parseInt((drupalSettings.algoliaSearch.itemsPerPage), 10)) {
        nbPages = Math.ceil(
          results.nbHits / parseInt((drupalSettings.algoliaSearch.itemsPerPage), 10),
        );
      }

      this.firstReceivedPage = 0;
      this.lastReceivedPage = props.defaultpageRender;
    } else if (this.firstReceivedPage === undefined
      || JSON.stringify(currentState) !== JSON.stringify(this.prevState)) {
      const $diff = diffObject(currentState, this.prevState);

      this.allResults = (this.allResults.length > 0 && Object.prototype.hasOwnProperty.call($diff, 'hitsPerPage'))
        ? [].concat(_toConsumableArray(this.allResults),
          _toConsumableArray(hitsWithPositionsAndQueryID))
        : _toConsumableArray(hitsWithPositionsAndQueryID);

      this.firstReceivedPage = page;
      this.lastReceivedPage = page;
    } else if (this.lastReceivedPage < page) {
      this.allResults = [].concat(
        _toConsumableArray(this.allResults),
        _toConsumableArray(hitsWithPositionsAndQueryID),
      );
      this.lastReceivedPage = page;
    } else if (this.firstReceivedPage > page) {
      this.allResults = [].concat(
        _toConsumableArray(hitsWithPositionsAndQueryID),
        _toConsumableArray(this.allResults),
      );
      this.firstReceivedPage = page;
    }

    this.prevState = currentState;
    const hasPrevious = this.firstReceivedPage > 0;
    const lastPageIndex = nbPages - 1;
    const hasMore = page < lastPageIndex;

    const refinePrevious = function refinePrevious(event) {
      return thisLocal.refine(event, thisLocal.firstReceivedPage - 1);
    };

    const refineNext = function refineNext(event) {
      return thisLocal.refine(event, thisLocal.lastReceivedPage + 1);
    };

    return {
      hits: this.allResults,
      hasPrevious,
      hasMore,
      refinePrevious,
      refineNext,
    };
  },
  getSearchParameters: function getSearchParameters(searchParameters, props, searchState) {
    return searchParameters.setQueryParameters({
      page: (typeof props.defaultpageRender === 'number') ? 0 : getCurrentRefinement(props, searchState, this.context) - 1,
      hitsPerPage: (typeof props.defaultpageRender === 'number') ? searchParameters.hitsPerPage * props.defaultpageRender : searchParameters.hitsPerPage,
    });
  },
  refine: function refine(props, searchState, event, index) {
    let finalIndex = index;
    if (typeof props.defaultpageRender === 'number' && props.defaultpageRender > 1) {
      finalIndex = props.defaultpageRender;
    } else if (finalIndex === undefined && this.lastReceivedPage !== undefined) {
      finalIndex = this.lastReceivedPage + 1;
    } else if (finalIndex === undefined) {
      finalIndex = getCurrentRefinement(props, searchState, this.context);
    }

    const id = getId();

    // `index` is indexed from 0 but page number is indexed from 1
    const nextValue = _defineProperty({}, id, finalIndex + 1);
    const resetPage = false;
    return refineValue(searchState, nextValue, this.context, resetPage);
  },
});
