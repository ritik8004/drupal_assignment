import React, { useRef, useEffect } from 'react';
import { Stats } from 'react-instantsearch-dom';
import ClearRefinements from '../filters/selectedfilters/ClearFilters';
import { updateAfter } from '../utils/utils';

/**
 * All filters displayed a slide toggle from right side.
 */
const AllFilters = (props) => {
  const allFiltersRef = useRef();

  useEffect(() => {
    // @todo: Check if we can avoid setTimeout and usage of updateAfter.
    setTimeout(() => {
      // Show selected with title accordion of filter, for the "All Filters"
      // main display, Which toggles with sliding effect from right side and
      // also used for mobile.
      if (typeof allFiltersRef.current == 'object') {
        const filters = allFiltersRef.current.querySelectorAll('.c-collapse-item');

        let hasSelection = false;
        filters.forEach(element => {
          const children = element.getElementsByTagName('ul')[0];

          if (typeof children !== 'undefined' && children.querySelector('li') === null) {
            element.classList.add('hide-facet-block');
          }
          else {
            element.classList.remove('hide-facet-block');
          }

          if (typeof children !== 'undefined') {
            const selectedFilters = children.querySelectorAll('li.is-active');
            // get all selected items for current filters.
            let currentSelection = [];
            [].forEach.call(selectedFilters, function (item) {
              // Replace count in parentesis with empty string.
              currentSelection.push(item.textContent.replace(/ *\([^)]*\) */g, ''));
            });

            // Get the currrent filter's title.
            const textContent = (element.getElementsByTagName('h3')[0].querySelector('span') === null)
              ? element.getElementsByTagName('h3')[0].textContent
              : element.getElementsByTagName('h3')[0].querySelector('span').innerHTML;

            // Prepares html content to display texts for only 2 items, and rest
            // Will be displayed as a count ((+5) selected.) in brackets. (i.e black, white (+2))
            if (currentSelection.length > 0) {
              hasSelection = true;
              const displayItems = (currentSelection.length > 2)
                ? currentSelection.slice(0, 2)
                : currentSelection;
              const additionalSelection = (currentSelection.length - 2);

              const additionalSelectionHtml = (additionalSelection > 0) ? '<span class="total-count"> (+' + additionalSelection + ')</span>' : '';
              element.getElementsByTagName('h3')[0].innerHTML = '<span>' + textContent + '</span>';
              element.getElementsByTagName('h3')[0].innerHTML += '<div class="selected-facets">'+
              '<span class="title">' + displayItems.join() + '</span>' +
              additionalSelectionHtml
              '</div>';
            }
            else {
              element.getElementsByTagName('h3')[0].innerHTML = '<span>' + textContent + '</span>';
            }
          }
        });

        if (hasSelection) {
          allFiltersRef.current.querySelector('.facet-clear-all').classList.add('has-link');
        }
        else {
          allFiltersRef.current.querySelector('.facet-clear-all').classList.remove('has-link');
        }
      }
    }, updateAfter);
  });

  return (
    <div className="block block-alshaya-search-api block-alshaya-search-facets-block-all">
      <div className="all-filters-algolia">
        <div className="filter__head">
          <div className="back-facet-list"></div>
          <div className="filter-sort-title">filter &amp; sort</div>
          <div className="all-filters-close"></div>
        </div>
        <div className="filter__inner" ref={allFiltersRef}>
          {props.children}
          <div className="filter__foot">
            <div className="facet-all-count">
              <div className="view-header search-count tablet">
                <Stats
                  translations={{
                    stats(nbHits, timeSpentMS) {
                      return `${nbHits} items`;
                    },
                  }}
                />
              </div>
            </div>
            <div className="facet-clear-all button"><ClearRefinements /></div>
            <a className="facet-apply-all button">apply filter</a>
          </div>
        </div>
      </div>
      <input type="hidden" id="all-filter-active-facet-sort" value="" />
    </div>
  );
};

export default AllFilters;
