import React, { useState } from 'react';
import { makeFacetAliasApiRequest } from '../../utils/requests';
import { facetFieldAlias, isMobile } from '../../utils';
import { isAddToBagEnabled } from '../../../../../js/utilities/addToBagHelper';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import StaticMinicart from '../../../../../js/utilities/components/static-minicart';

/**
 * Sticky filters.
 */
const StickyFilterWrapper = React.forwardRef(({ callback, pageType = null }, ref) => {
  const [filters, setFilters] = useState([]);
  const filtersCallBack = ({ activeFilters, limit }) => {
    // Make api call to get facet values alias to update facets  pretty paths,
    // on page load when we have all the active filters.
    if (activeFilters.length > filters.length) {
      activeFilters.forEach((activeFilter) => {
        if (activeFilter.id === 'sort_by') {
          return;
        }

        const facetAlias = facetFieldAlias(activeFilter.id, 'alias', pageType);
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

      { /* Add static minicart for the desktop view if addToBag feature enabled. */}
      <ConditionalView condition={!isMobile() && isAddToBagEnabled()}>
        <StaticMinicart />
      </ConditionalView>
    </div>
  );
});

export default StickyFilterWrapper;
