import _defineProperty from "@babel/runtime/helpers/esm/defineProperty";
import _toConsumableArray from "@babel/runtime/helpers/esm/toConsumableArray";
import _objectWithoutProperties from "@babel/runtime/helpers/esm/objectWithoutProperties";
import _isEqual from "lodash/isEqual";
import _reduce from "lodash/reduce";
import _isPlainObject from "lodash/isPlainObject";
import { createConnector } from 'react-instantsearch-dom';
import {
  getCurrentRefinementValue,
  refineValue,
  getResults,
  addAbsolutePositions,
  addQueryID
} from '../../../utils/indexUtils';

function getId() {
  return 'page';
}

function getCurrentRefinement(props, searchState, context) {
  var id = getId();
  var page = 1;
  var currentRefinement = getCurrentRefinementValue(props, searchState, context, id, page);

  if (typeof props.defaultpageRender === 'number') {
    return props.defaultpageRender;
  }

  if (typeof currentRefinement === 'string') {
    return parseInt(currentRefinement, 10);
  }

  return currentRefinement;
}

function diffObject(obj1, obj2) {
  return _reduce(obj1, function(result, value, key) {
    if (_isPlainObject(value)) {
      result[key] = diffObject(value, obj2[key]);
    } else if ((typeof obj2 !== 'undefined' && typeof obj2[key] !== 'undefined') && !_isEqual(value, obj2[key])) {
      result[key] = value;
    }
    return result;
  }, {});
};

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
    var _this = this;

    var results = getResults(searchResults, this.context);
    this._allResults = this._allResults || [];
    this._prevState = this._prevState || {};

    if (!results) {
      return {
        hits: [],
        hasPrevious: false,
        hasMore: false,
        defaultpageRender: false,
        refine: function refine() {},
        refinePrevious: function refinePrevious() {},
        refineNext: function refineNext() {}
      };
    }

    var page = (this._firstReceivedPage === undefined && typeof props.defaultpageRender === 'number') ? props.defaultpageRender - 1 : results.page,
        hits = results.hits,
        hitsPerPage = results.hitsPerPage,
        nbPages = results.nbPages,
        _results$_state = results._state;
    _results$_state = _results$_state === void 0 ? {} : _results$_state;

    var p = _results$_state.page,
        currentState = _objectWithoutProperties(_results$_state, ["page"]);

    var hitsWithPositions = addAbsolutePositions(hits, hitsPerPage, page);
    var hitsWithPositionsAndQueryID = addQueryID(hitsWithPositions, results.queryID);

    if (this._firstReceivedPage === undefined && typeof props.defaultpageRender === 'number') {
      this._allResults = _toConsumableArray(hitsWithPositionsAndQueryID);
      // Used Drupal settings to know the pages based on set number of items.
      if (results.nbHits > parseInt(drupalSettings.algoliaSearch.itemsPerPage)) {
        nbPages = Math.ceil(results.nbHits / parseInt(drupalSettings.algoliaSearch.itemsPerPage));
      }

      this._firstReceivedPage = 0;
      this._lastReceivedPage = props.defaultpageRender;
    }
    else if (this._firstReceivedPage === undefined || !_isEqual(currentState, this._prevState)) {
      const $diff = diffObject(currentState, this._prevState);

      this._allResults = (this._allResults.length > 0 && $diff.hasOwnProperty('hitsPerPage'))
        ? [].concat(_toConsumableArray(this._allResults), _toConsumableArray(hitsWithPositionsAndQueryID))
        : _toConsumableArray(hitsWithPositionsAndQueryID);

      this._firstReceivedPage = page;
      this._lastReceivedPage = page;
    } else if (this._lastReceivedPage < page) {
      this._allResults = [].concat(_toConsumableArray(this._allResults), _toConsumableArray(hitsWithPositionsAndQueryID));
      this._lastReceivedPage = page;
    } else if (this._firstReceivedPage > page) {
      this._allResults = [].concat(_toConsumableArray(hitsWithPositionsAndQueryID), _toConsumableArray(this._allResults));
      this._firstReceivedPage = page;
    }

    this._prevState = currentState;
    var hasPrevious = this._firstReceivedPage > 0;
    var lastPageIndex = nbPages - 1;
    var hasMore = page < lastPageIndex;

    var refinePrevious = function refinePrevious(event) {
      return _this.refine(event, _this._firstReceivedPage - 1);
    };

    var refineNext = function refineNext(event) {
      return _this.refine(event, _this._lastReceivedPage + 1);
    };

    return {
      hits: this._allResults,
      hasPrevious: hasPrevious,
      hasMore: hasMore,
      refinePrevious: refinePrevious,
      refineNext: refineNext
    };
  },
  getSearchParameters: function getSearchParameters(searchParameters, props, searchState) {
    return searchParameters.setQueryParameters({
      page: (typeof props.defaultpageRender === 'number') ? 0 : getCurrentRefinement(props, searchState, this.context) - 1,
      hitsPerPage: (typeof props.defaultpageRender === 'number') ? searchParameters.hitsPerPage * props.defaultpageRender : searchParameters.hitsPerPage,
    });
  },
  refine: function refine(props, searchState, event, index) {
    if (typeof props.defaultpageRender === 'number' && props.defaultpageRender > 1) {
      index = props.defaultpageRender;
    } else if (index === undefined && this._lastReceivedPage !== undefined) {
      index = this._lastReceivedPage + 1;
    } else if (index === undefined) {
      index = getCurrentRefinement(props, searchState, this.context);
    }

    var id = getId();

    var nextValue = _defineProperty({}, id, index + 1); // `index` is indexed from 0 but page number is indexed from 1
    var resetPage = false;
    return refineValue(searchState, nextValue, this.context, resetPage);
  }
});
