import React, { useState, useRef } from 'react';
import { getFilters } from '../../utils';
import WidgetManager from '../widget-manager';

const Filters = ({ indexName, pageType, ...props }) => {
  const [filterCounts, setfilters] = useState([]);
  const ref = useRef();

  // Loop through all the filters given in config and prepare an array of filters.
  const updateFilterResult = (itm) => {
    filterCounts[itm.attr] = itm.count;
    setfilters(filterCounts);
    if (typeof ref.current === 'object' && ref.current !== null) {
      const filters = ref.current.querySelectorAll('.c-collapse-item');
      const activeFilters = [];
      filters.forEach((element) => {
        const children = element.getElementsByTagName('ul')[0];
        // Do not show facets that have a single value if the render_single_result_facets is false.
        if (!drupalSettings.algoliaSearch.render_single_result_facets) {
          const exclude = drupalSettings.algoliaSearch.exclude_render_single_result_facets ? drupalSettings.algoliaSearch.exclude_render_single_result_facets.trim().split(',') : '';
          // Certain factes should always be rendered irrespective of render_single_result_facets.
          // So we only consider the attributes not part of the exclude_render_single_result_facets.
          // Sort is not filter attribute but we always need to show it.
          if (exclude.length > 0 && element.getAttribute('id') !== 'sort_by') {
            if ((!exclude.includes(element.getAttribute('id')) && children.childElementCount <= 1)) {
              element.classList.add('hide-facet-block');
            } else {
              element.classList.remove('hide-facet-block');
              activeFilters.push(element);
            }
          }
        } else if (typeof children !== 'undefined' && children.querySelector('li') === null) {
          element.classList.add('hide-facet-block');
        } else {
          activeFilters.push(element);
          element.classList.remove('hide-facet-block');
        }
      });

      props.callback({ activeFilters, filterCounts, ...props });
    }
  };

  const facets = [];
  getFilters(pageType).forEach((facet) => {
    facets.push(
      <WidgetManager
        key={facet.identifier}
        facet={facet}
        indexName={indexName}
        filterResult={(test) => updateFilterResult(test)}
        pageType={pageType}
      />,
    );
  });

  return (
    <div ref={ref} className="filter-facets">
      {facets}
    </div>
  );
};

export default Filters;
