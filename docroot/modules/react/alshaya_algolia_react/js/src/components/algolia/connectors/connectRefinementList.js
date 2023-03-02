// Copied from: https://github.com/algolia/react-instantsearch/blob/v5.7.0
// https://github.com/algolia/react-instantsearch/blob/v5.7.0/packages/react-instantsearch-core/src/connectors/connectRefinementList.js
// As we didn't want to limit the facets to a certain limit. the default widget
// connector does not allow a configuration to get unlimited facets.
// We copied the connector and commented the default limit from defaultProps.
import PropTypes from 'prop-types';
import { createConnector } from 'react-instantsearch-dom';
import {
  cleanUpValue,
  getIndexId,
  refineValue,
  getCurrentRefinementValue,
  getResults,
} from '../../../utils/indexUtils';

const namespace = 'refinementList';

function getId(props) {
  return props.attribute;
}

function getCurrentRefinement(props, searchState, context) {
  const currentRefinement = getCurrentRefinementValue(
    props,
    searchState,
    context,
    `${namespace}.${getId(props)}`,
    [],
  );

  if (typeof currentRefinement !== 'string') {
    return currentRefinement;
  }

  if (currentRefinement) {
    return [currentRefinement];
  }

  return [];
}

function getValue(name, props, searchState, context) {
  const currentRefinement = getCurrentRefinement(props, searchState, context);
  const isAnewValue = currentRefinement.indexOf(name) === -1;
  const nextRefinement = isAnewValue
    ? currentRefinement.concat([name]) // cannot use .push(), it mutates
    : currentRefinement.filter(
      (selectedValue) => selectedValue !== name,
    ); // cannot use .splice(), it mutates
  return nextRefinement;
}

function getLimit({ showMore, limit, showMoreLimit }) {
  return showMore ? showMoreLimit : limit;
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

function cleanUp(props, searchState, context) {
  return cleanUpValue(searchState, context, `${namespace}.${getId(props)}`);
}
/**
 * connectRefinementList connector provides the logic to build a widget that will
 * give the user the ability to choose multiple values for a specific facet.
 * @name connectRefinementList
 * @kind connector
 * @requirements The attribute passed to the `attribute` prop must be present in "attributes for
 * faceting"
 * on the Algolia dashboard or configured as `attributesForFaceting` via a set settings call to the
 * Algolia API.
 * @propType {string} attribute - the name of the attribute in the record
 * @propType {boolean} [searchable=false] - allow search inside values
 * @propType {string} [operator=or] - How to apply the refinements. Possible values: 'or' or 'and'.
 * @propType {boolean} [showMore=false] - true if the component should display a button that will
 * expand the number of items
 * @propType {number} [limit=10] - the minimum number of displayed items
 * @propType {number} [showMoreLimit=20] - the maximun number of displayed items. Only used when
 * showMore is set to `true`
 * @propType {string[]} defaultRefinement - the values of the items selected by default.
 * The searchState of this widget takes the form of a list of `string`s, which correspond
 * to the values of all selected refinements. However, when there are no refinements selected,
 * the value of the searchState is an empty string.
 * @propType {function} [transformItems] - Function to modify the items being displayed, e.g. for
 * filtering or sorting them. Takes an items as parameter and expects it back in return.
 * @providedPropType {function} refine - a function to toggle a refinement
 * @providedPropType {function} createURL - a function to generate a URL for the corresponding
 * search state
 * @providedPropType {string[]} currentRefinement - the refinement currently applied
 * @providedPropType {array.<{count: number, isRefined: boolean, label: string, value: string}>}
 * items - the list of items the RefinementList can display.
 * @providedPropType {function} searchForItems - a function to toggle a search inside items values
 * @providedPropType {boolean} isFromSearch - a boolean that says if the `items` props contains
 * facet values from the global search or from the search inside items.
 * @providedPropType {boolean} canRefine - a boolean that says whether you can refine
 */

const sortBy = ['isRefined', 'count:desc', 'name:asc'];
export default createConnector({
  displayName: 'AlgoliaRefinementList',

  propTypes: {
    id: PropTypes.string,
    attribute: PropTypes.string.isRequired,
    operator: PropTypes.oneOf(['and', 'or']),
    showMore: PropTypes.bool,
    limit: PropTypes.number,
    showMoreLimit: PropTypes.number,
    defaultRefinement: PropTypes.arrayOf(
      PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    ),
    searchable: PropTypes.bool,
    transformItems: PropTypes.func,
  },

  defaultProps: {
    operator: 'or',
    showMore: false,
    // limit: 10,
    showMoreLimit: 20,
  },

  getProvidedProps(
    props,
    searchState,
    searchResults,
    metadata,
    searchForFacetValuesResults,
  ) {
    const { attribute, searchable } = props;
    const results = getResults(searchResults, this.context);

    const canRefine = Boolean(results) && Boolean(results.getFacetByName(attribute));

    const isFromSearch = Boolean(
      searchForFacetValuesResults
        && searchForFacetValuesResults[attribute]
        && searchForFacetValuesResults.query !== '',
    );

    // Search For Facet Values is not available with derived helper (used for multi index search)
    if (searchable && this.context.multiIndexContext) {
      throw new Error(
        'react-instantsearch: searching in *List is not available when used inside a'
          + ' multi index context',
      );
    }

    if (!canRefine) {
      return {
        items: [],
        currentRefinement: getCurrentRefinement(
          props,
          searchState,
          this.context,
        ),
        canRefine,
        isFromSearch,
        searchable,
      };
    }

    // Sort configured attributes by name.
    const { attributesToSortByName } = drupalSettings.algoliaSearch;
    let sortOrder = sortBy;

    if (
      attributesToSortByName
      && attributesToSortByName.length
      && attributesToSortByName.indexOf(attribute) > -1
    ) {
      sortOrder = ['isRefined', 'name:asc', 'count:desc'];
    }

    const items = isFromSearch
      ? searchForFacetValuesResults[attribute].map((v) => ({
        label: v.value,
        value: getValue(v.value, props, searchState, this.context),
        _highlightResult: { label: { value: v.highlighted } },
        count: v.count,
        isRefined: v.isRefined,
      }))
      : results.getFacetValues(attribute, { sortBy: sortOrder }).map((v) => ({
        label: v.name,
        value: getValue(v.name, props, searchState, this.context),
        count: v.count,
        isRefined: v.isRefined,
      }));

    const transformedItems = props.transformItems
      ? props.transformItems(items)
      : items;

    return {
      items: transformedItems.slice(0, getLimit(props)),
      currentRefinement: getCurrentRefinement(props, searchState, this.context),
      isFromSearch,
      searchable,
      canRefine: transformedItems.length > 0,
    };
  },

  refine(props, searchState, nextRefinement) {
    return refine(props, searchState, nextRefinement, this.context);
  },

  searchForFacetValues(props, searchState, nextRefinement) {
    return {
      facetName: props.attribute,
      query: nextRefinement,
      maxFacetHits: getLimit(props),
    };
  },

  cleanUp(props, searchState) {
    return cleanUp(props, searchState, this.context);
  },

  getSearchParameters(searchParameters, props, searchState) {
    const { attribute, operator } = props;

    const addKey = operator === 'and' ? 'addFacet' : 'addDisjunctiveFacet';
    const addRefinementKey = `${addKey}Refinement`;

    const finalSearchParameters = searchParameters[addKey](attribute);

    return getCurrentRefinement(props, searchState, this.context).reduce(
      (res, val) => res[addRefinementKey](attribute, val),
      finalSearchParameters,
    );
  },

  getMetadata(props, searchState) {
    const id = getId(props);
    const { context } = this;
    return {
      id,
      index: getIndexId(this.context),
      items:
        getCurrentRefinement(props, searchState, context).length > 0
          ? [
            {
              attribute: props.attribute,
              label: `${props.attribute}: `,
              currentRefinement: getCurrentRefinement(
                props,
                searchState,
                context,
              ),
              value: (nextState) => refine(props, nextState, [], context),
              items: getCurrentRefinement(props, searchState, context).map(
                (item) => ({
                  label: `${item}`,
                  value: (nextState) => {
                    const nextSelectedItems = getCurrentRefinement(
                      props,
                      nextState,
                      context,
                    ).filter((other) => other !== item);
                    return refine(
                      props,
                      searchState,
                      nextSelectedItems,
                      context,
                    );
                  },
                }),
              ),
            },
          ]
          : [],
    };
  },
});
