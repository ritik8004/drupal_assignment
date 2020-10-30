import React, { useRef } from 'react';
import { Stats } from 'react-instantsearch-dom';
import ClearRefinements from '../algolia/ClearFilters';

/**
 * All filters displayed a slide toggle from right side.
 */
const AllFilters = ({
  wrapperClassName, AllFilterClass, pageType = null, children: propChildren,
}) => {
  const allFiltersRef = useRef();
  const propWrapperClass = (typeof wrapperClassName !== 'undefined') ? wrapperClassName : '';
  const propAllFilterClass = (typeof AllFilterClass !== 'undefined') ? AllFilterClass : '';
  const className = `block ${propWrapperClass}`;

  // Show selected with title accordion of filter, for the "All Filters"
  // main display, Which toggles with sliding effect from right side and
  // also used for mobile.
  const filtersCallBack = ({ activeFilters }) => {
    let hasSelection = false;
    activeFilters.forEach((element) => {
      const children = element.getElementsByTagName('ul')[0];

      if (typeof children !== 'undefined' && children.querySelector('li') === null) {
        element.classList.add('hide-facet-block');
      } else {
        element.classList.remove('hide-facet-block');
      }

      if (typeof children !== 'undefined') {
        const selectedFilters = children.querySelectorAll('li.is-active');
        // get all selected items for current filters.
        const currentSelection = [];
        [].forEach.call(selectedFilters, (item) => {
          // Replace count in parentesis with empty string.
          currentSelection.push(item.textContent.replace(/ *\([^)]*\) */g, ''));
        });

        // Get the currrent filter's title.
        const textContent = (element.getElementsByTagName('h3')[0].querySelector('span') === null)
          ? element.getElementsByTagName('h3')[0].textContent
          : element.getElementsByTagName('h3')[0].querySelector('span').innerHTML;

        const selectedElm = element;

        // Prepares html content to display texts for only 2 items, and rest
        // Will be displayed as a count ((+5) selected.) in brackets. (i.e black, white (+2))
        if (currentSelection.length > 0) {
          hasSelection = true;
          const displayItems = (currentSelection.length > 2)
            ? currentSelection.slice(0, 2)
            : currentSelection;
          const additionalSelection = (currentSelection.length - 2);

          const additionalSelectionHtml = (additionalSelection > 0) ? `<span class="total-count"> (+${additionalSelection})</span>` : '';
          selectedElm.getElementsByTagName('h3')[0].innerHTML = `<span>${textContent}</span>`;
          selectedElm.getElementsByTagName('h3')[0].innerHTML += `${'<div class="selected-facets">'
          + '<span class="title">'}${displayItems.join(', ')}</span>${
            additionalSelectionHtml
          }</div>`;
        } else {
          selectedElm.getElementsByTagName('h3')[0].innerHTML = `<span>${textContent}</span>`;
        }
      }
    });

    if (typeof allFiltersRef.current === 'object' && allFiltersRef.current !== null) {
      if (hasSelection) {
        allFiltersRef.current.querySelector('.facet-clear-all').classList.add('has-link');
      } else {
        allFiltersRef.current.querySelector('.facet-clear-all').classList.remove('has-link');
      }
    }
  };

  return (
    <div className={className}>
      <div className={propAllFilterClass}>
        <div className="filter__head">
          <div className="back-facet-list" style={{ display: 'none' }} />
          <div className="filter-sort-title">{Drupal.t('filter & sort')}</div>
          <div className="all-filters-close" />
        </div>
        <div className="filter__inner" ref={allFiltersRef}>
          {propChildren(filtersCallBack)}
          <div className="filter__foot">
            <div className="facet-all-count">
              <div className="view-header search-count tablet">
                <Stats
                  translations={{
                    stats(nbHits) {
                      return Drupal.t('@total items', { '@total': nbHits });
                    },
                  }}
                />
              </div>
            </div>
            <div className="facet-clear-all button"><ClearRefinements title={Drupal.t('clear all')} pageType={pageType} /></div>
            <a className="facet-apply-all button">{Drupal.t('apply filter')}</a>
          </div>
        </div>
      </div>
      <input type="hidden" id="all-filter-active-facet-sort" value="" />
    </div>
  );
};

export default AllFilters;
