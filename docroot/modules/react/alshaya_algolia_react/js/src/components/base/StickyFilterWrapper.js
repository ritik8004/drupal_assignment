import React, { useState } from 'react';
import { makeFacetAliasApiRequest } from '../../utils/requests';
import { facetFieldAlias } from '../../utils';

/**
 * Sticky filters.
 */
const StickyFilterWrapper = React.forwardRef(({ callback }, ref) => {
  const [filters, setFilters] = useState([]);
  const { subCategories } = drupalSettings.algoliaSearch;

  const filtersCallBack = ({ activeFilters, limit }) => {
    // Make api call to get facet values alias to update facets  pretty paths,
    // on page load when we have all the active filters.
    if (activeFilters.length > filters.length) {
      activeFilters.forEach((activeFilter) => {
        if (activeFilter.id === 'sort_by') {
          return;
        }

        const facetAlias = facetFieldAlias(activeFilter.id, 'alias');
        if (filters.indexOf(facetAlias) < 0) {
          filters.push(facetAlias);
          makeFacetAliasApiRequest(facetAlias);
        }
      });
      setFilters(filters);
    }

    if (activeFilters.length > limit) {
      const hideFilters = activeFilters.slice(limit);
      hideFilters.forEach((filter) => {
        filter.classList.add('hide-facet-block');
      });
    }

    // Hide the `all filters` link when less filters (only for desktop).
    if (activeFilters.length <= limit) {
      ref.current.querySelector('.show-all-filters-algolia').classList.add('hide-for-desktop');
    } else {
      ref.current.querySelector('.show-all-filters-algolia').classList.remove('hide-for-desktop');
    }
  };

  return (
    <div className="sticky-filter-wrapper">
      <div className="container-without-product" ref={ref}>
        {callback(filtersCallBack)}
      </div>
    </div>
  );
});

export default StickyFilterWrapper;
