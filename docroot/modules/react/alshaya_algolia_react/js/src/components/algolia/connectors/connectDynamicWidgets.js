// Copied from node_modules/react-instantsearch-core/dist/es/connectors/connectDynamicWidgets.js
// Along with attributesToRender we also want to pass userData from algolia
// query result. This userData contains rule context with custom data for facets
// display like sort by indexes, options, labels etc.
import PropTypes from 'prop-types';
import { createConnector } from 'react-instantsearch-dom';
// @ts-ignore
import { getResults } from '../../../utils/indexUtils';
import logger from '../../../../../../js/utilities/logger';

// The default number of facet values to request.
const MAX_WILDCARD_FACETS = 20;
export default createConnector({
  displayName: 'AlgoliaDynamicWidgets',
  $$type: 'ais.dynamicWidgets',
  defaultProps: {
    transformItems: function transformItems(items) {
      return items;
    },
    // The default number of facet values to request.
    // To prevent an additional network request when a widget mounts,
    // it’s recommended to set this value as high as the highest limit and
    // showMoreLimit of the dynamic widgets.
    // To avoid pinned items not showing in the result,
    // make sure you choose a maxValuesPerFacet as high as all the most pinned items you have.
    maxValuesPerFacet: 20,
  },
  propTypes: {
    transformItems: PropTypes.func,
    facets: PropTypes.arrayOf(PropTypes.string),
    maxValuesPerFacet: PropTypes.number,
  },
  getProvidedProps: function getProvidedProps(props, _searchState, searchResults) {
    const results = getResults(searchResults, {
      ais: props.contextValue,
      multiIndexContext: props.indexContextValue,
    });
    if (props.facets && !(Array.isArray(props.facets) && props.facets.length <= 1 && (props.facets[0] === '*' || props.facets[0] === undefined))) {
      throw new Error('The `facets` prop only accepts [] or ["*"], you passed '.concat(JSON.stringify(props.facets)));
    }
    if (!results) {
      return {
        attributesToRender: [],
      };
    }
    // eslint-disable-next-line max-len
    const facetOrder = (results.renderingContent && results.renderingContent.facetOrdering && results.renderingContent.facetOrdering.facets && results.renderingContent.facetOrdering.facets.order) || [];
    const attributesToRender = props.transformItems(facetOrder, {
      results,
    });
    if (attributesToRender.length > MAX_WILDCARD_FACETS && !props.facets) {
      // eslint-disable-next-line no-console
      console.warn('More than '.concat(MAX_WILDCARD_FACETS, " facets are requested to be displayed without explicitly setting which facets to retrieve. This could have a performance impact. Set \"facets\" to [] to do two smaller network requests, or explicitly to ['*'] to avoid this warning."));
    }
    // eslint-disable-next-line no-underscore-dangle
    if (props.maxValuesPerFacet < results._state.maxValuesPerFacet) {
      // eslint-disable-next-line no-console,no-underscore-dangle
      logger.warn('The maxValuesPerFacet set by dynamic widgets ('.concat(props.maxValuesPerFacet, ') is smaller than one of the limits set by a widget (').concat(results._state.maxValuesPerFacet, '). This causes a mismatch in query parameters and thus an extra network request when that widget is mounted.'));
    }

    const { userData } = results;

    // Add sort-by attribute explicitly at the top of facet list
    // as we want to keep sort-by facet out of facet display configuration
    // on algolia.
    if (attributesToRender.indexOf('sort_by') === -1) {
      attributesToRender.unshift('sort_by');
    }

    return {
      attributesToRender,
      userData,
    };
  },
  getSearchParameters: function getSearchParameters(searchParameters, props) {
    return (props.facets || ['*']).reduce((acc, curr) => acc.addFacet(curr), searchParameters.setQueryParameters({
      maxValuesPerFacet: Math.max(
        props.maxValuesPerFacet || 0,
        searchParameters.maxValuesPerFacet || 0,
      ),
    }));
  },
});
