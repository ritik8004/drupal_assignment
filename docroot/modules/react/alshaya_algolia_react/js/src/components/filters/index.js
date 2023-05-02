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
        const ulElement = element.getElementsByTagName('ul');
        const childrenLi = ulElement[0] ? ulElement[0].querySelector('li') : null;
        if (ulElement.length === 0 || childrenLi === null) {
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
