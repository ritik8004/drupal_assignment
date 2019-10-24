import React, { useRef, useEffect } from 'react';
import { updateAfter } from '../utils';

/**
 * Sticky filters.
 */
const StickyFilter = (props) => {
  const stickyFiltersRef = useRef();

  useEffect(() => {
    // Show only maximum 4 filters for desktop sticky filter
    // excluding the sort by, (with sort by 5).
    setTimeout(() => {
      if (typeof stickyFiltersRef.current == 'object' && stickyFiltersRef.current !== null) {
        const filters = stickyFiltersRef.current.querySelectorAll('.c-collapse-item');
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

        if (activeFilters.length > 5) {
          var hideFilters = activeFilters.slice(5);
          hideFilters.forEach((filter) => {
            filter.classList.add('hide-facet-block');
          });
        }

        // Hide the `all filters` link when less filters (only for desktop).
        if (activeFilters.length <= 5) {
          stickyFiltersRef.current.querySelector('.show-all-filters-algolia').classList.add('hide-for-desktop');
        }
        else {
          stickyFiltersRef.current.querySelector('.show-all-filters-algolia').classList.remove('hide-for-desktop');
        }

        Drupal.algoliaReact.facetEffects();
        if ($(window).width() > 767 && stickyFiltersRef.current.parentNode.querySelector('.site-brand-home') === null) {
          var site_brand = $('.site-brand-home').clone();
          $(site_brand).insertBefore(stickyFiltersRef.current);
        }
      }
    }, updateAfter);
  }, [props.children]);

  return (
    <div className="sticky-filter-wrapper">
      <div className="container-without-product" ref={stickyFiltersRef}>
          {props.children}
      </div>
    </div>
  );
};

export default StickyFilter;
