import PropTypes from 'prop-types';
import { createConnector } from 'react-instantsearch-dom';
import {
  cleanUpValue,
  getCurrentRefinementValue,
  getIndexId,
  getResults,
  refineValue
} from '../../../utils/indexUtils';
const _ = require("lodash");

const namespace = 'multiRange';

function getId(props) {
  return props.attribute;
}

function getCurrentRefinement(props, searchState, context) {
  return getCurrentRefinementValue(
    props,
    searchState,
    context,
    `${namespace}.${getId(props)}`,
    [],
    currentRefinement => {
      if (currentRefinement === '') {
        return '';
      }
      return currentRefinement;
    }
  );
}

function refine(props, searchState, nextRefinement, context) {
  const id = getId(props);
  // Setting the value to an empty string ensures that it is persisted in
  // the URL as an empty value.
  // This is necessary in the case where `defaultRefinement` contains one
  // item and we try to deselect it. `nextSelected` would be an empty array,
  // which would not be persisted to the URL.
  // {foo: ['bar']} => "foo[0]=bar"
  // {foo: []} => ""
  const nextValue = { [id]: nextRefinement.length > 0 ? nextRefinement : '' };
  const resetPage = true;
  return refineValue(searchState, nextValue, context, resetPage, namespace);
}

// Copied from \Drupal\alshaya_search\Plugin\facets\query_type\AlshayaSearchGranular::getRange().
// and modified logic for startvalue.
// Ex: For a granularity of 5 and value of 0, range = 0-5.
// Ex: For a granularity of 5 and value of 5, range = 0-5.
// Ex: For a granularity of 5 and value of 9, range = 5-10.
function getRange(currentvalue, granularity) {
  // Initial values.
  var startvalue = 0;
  var stopvalue = granularity;

  if (currentvalue % granularity) {
    startvalue = currentvalue - (currentvalue % granularity);
  }
  else if (currentvalue > 0 ) {
    startvalue = currentvalue - (granularity - (currentvalue % granularity));
  }
  else {
    startvalue = currentvalue
  }

  stopvalue = startvalue + granularity;

  return {
    'start': parseFloat(startvalue),
    'end': parseFloat(stopvalue),
  };
}

function cleanUp(props, searchState, context) {
  return cleanUpValue(searchState, context, `${namespace}.${getId(props)}`);
}

function parseItem(value) {
  if (value.length === 0) {
    return { start: null, end: null };
  }
  const [startStr, endStr] = value.split(':');
  return {
    start: startStr.length > 0 ? parseInt(startStr, 10) : null,
    end: endStr.length > 0 ? parseInt(endStr, 10) : null,
  };
}

function stringifyItem(item) {
  if (typeof item.start === 'undefined' && typeof item.end === 'undefined') {
    return '';
  }
  return (item.start ? item.start : '') + ':' + (item.end ? item.end : '');
}

function getLimit(_ref) {
  var showMore = _ref.showMore,
      limit = _ref.limit,
      showMoreLimit = _ref.showMoreLimit;
  return showMore ? showMoreLimit : limit;
}

const sortBy = ['isRefined', 'count:desc'];

export default createConnector({
  displayName: 'AlgoliaPriceRefinement',

  propTypes: {
    id: PropTypes.string,
    attribute: PropTypes.string.isRequired,
    operator: PropTypes.oneOf(['and', 'or']),
    defaultRefinement: PropTypes.arrayOf(
      PropTypes.oneOfType([PropTypes.string, PropTypes.number])
    ),
    granularity: PropTypes.number,
  },

  defaultProps: {
    operator: 'or',
  },

  getProvidedProps(props, searchState, searchResults, metadata, searchForFacetValuesResults) {
    const { attribute, granularity } = props;
    const results = getResults(searchResults, this.context);

    const currentRefinement = getCurrentRefinement(
      props,
      searchState,
      this.context
    );

    const canRefine =
      Boolean(results) && Boolean(results.getFacetByName(attribute));

    if (!canRefine) {
      return {
        items: [],
        currentRefinement: currentRefinement,
        canRefine
      };
    }

    var newItems = [];
    results.getFacetValues(attribute, { sortBy }).forEach(v => {
      const range = getRange(parseFloat(v.name), parseInt(granularity));
      const rangeKey = stringifyItem(range);
      const key = _.findKey(newItems, {label: rangeKey});

      if (typeof key === 'undefined') {
        var object = {
          label: rangeKey,
          value: rangeKey,
          sort: range.start == null ? 0 : parseInt(range.start),
          count: parseInt(v.count),
          isRefined: rangeKey === currentRefinement
        };
        newItems.push(object);
      }
      else {
        newItems[key].count = newItems[key].count + parseInt(v.count);
      }
    });

    const sortedItems = _.sortBy(newItems, ['sort']);

    return {
      items: sortedItems,
      currentRefinement: getCurrentRefinement(props, searchState, this.context),
      canRefine: sortedItems.length > 0,
    };
  },

  refine(props, searchState, nextRefinement) {
    return refine(props, searchState, nextRefinement, this.context);
  },

  searchForFacetValues(props, searchState, nextRefinement) {
    return {
      facetName: props.attribute,
      query: nextRefinement,
      maxFacetHits: getLimit(props)
    };
  },

  getSearchParameters(searchParameters, props, searchState) {
    const { attribute } = props;
    const { start, end } = parseItem(
      getCurrentRefinement(props, searchState, this.context)
    );
    searchParameters = searchParameters.addDisjunctiveFacet(attribute);

    if (start) {
      searchParameters = searchParameters.addNumericRefinement(
        attribute,
        '>',
        start
      );
    }
    if (end) {
      searchParameters = searchParameters.addNumericRefinement(
        attribute,
        '<=',
        end
      );
    }

    return searchParameters;
  },

  cleanUp(props, searchState) {
    return cleanUp(props, searchState, this.context);
  },

  getMetadata(props, searchState) {
    const id = getId(props);
    const value = getCurrentRefinement(props, searchState, this.context);
    const index = getIndexId(this.context);
    const items = [];

    if (value.length > 0) {
      items.push({
        label: `${props.attribute}: ${value}`,
        attribute: props.attribute,
        currentRefinement: value,
        value: nextState => refine(props, nextState, '', this.context),
      });
    }
    return { id, index, items };
  },

});
