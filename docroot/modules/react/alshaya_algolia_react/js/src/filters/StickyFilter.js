import React, { useRef, useEffect } from 'react';

/**
 * Sticky filters.
 */
const StickyFilter = (props) => {
  const allFiltersRef = useRef();

  useEffect(() => {
    setTimeout(() => {
      // Show only maximum 4 filters for desktop sticky filter
      // excluding the sort by, (with sort by 5).
      const filters = allFiltersRef.current.querySelectorAll('.c-accordion');
      let activeFilters = [];
      filters.forEach(element => {
        const children = element.getElementsByTagName('ul')[0];

        if (typeof children !== 'undefined' && children.querySelector('li') === null) {
          element.classList.add('hide-facet-block');
        }
        else {
          activeFilters.push(element);
          element.classList.remove('hide-facet-block');
        }
      });

      if (activeFilters.length > 3) {
        var hideFilters = activeFilters.slice(3);
        hideFilters.forEach((filter) => {
          filter.classList.add('hide-facet-block');
        });
      }

      // Hide the `all filters` link when less filters (only for desktop).
      if (activeFilters.length <= 3) {
        allFiltersRef.current.querySelector('.show-all-filters').classList.add('hide-for-desktop');
      }
      else {
        allFiltersRef.current.querySelector('.show-all-filters').classList.remove('hide-for-desktop');
      }
    }, 500);
  });

  return (
    <div className="sticky-filter-wrapper">
        <div className="container-without-product" ref={allFiltersRef}>
          {props.children}
      </div>
    </div>
  );
};

export default StickyFilter;
