import React, { useRef, useEffect } from 'react';
import { isMobile } from '../../utils';

/**
 * Sticky filters.
 */
const StickyFilter = (props) => {
  const stickyFiltersRef = useRef();

  useEffect(() => {
    Drupal.algoliaReact.facetEffects();
    const stickyFilterWrapper = stickyFiltersRef.current.parentNode;
    if (!isMobile() && stickyFilterWrapper.querySelector('.site-brand-home') === null) {
      var site_brand = document.querySelector('.site-brand-home').cloneNode(true);
      stickyFilterWrapper.insertBefore(site_brand, stickyFilterWrapper.childNodes[0]);
    }
  }, [props.children]);

  const filtersCallBack = ({activeFilters, limit}) => {
    if (activeFilters.length > limit) {
      var hideFilters = activeFilters.slice(limit);
      hideFilters.forEach((filter) => {
        filter.classList.add('hide-facet-block');
      });
    }

    // Hide the `all filters` link when less filters (only for desktop).
    if (activeFilters.length <= limit) {
      stickyFiltersRef.current.querySelector('.show-all-filters-algolia').classList.add('hide-for-desktop');
    }
    else {
      stickyFiltersRef.current.querySelector('.show-all-filters-algolia').classList.remove('hide-for-desktop');
    }
  }

  return (
    <div className="sticky-filter-wrapper">
      <div className="container-without-product" ref={stickyFiltersRef}>
        {props.children(filtersCallBack)}
      </div>
    </div>
  );
};

export default StickyFilter;
