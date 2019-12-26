import React, { useState, useRef } from 'react';
import { getFilters } from '../../utils';
import WidgetManager from '../widget-manager';

export default ({indexName, ...props}) => {
  const [filterCounts, setfilters] = useState([]);
  const ref = useRef();
  // Loop through all the filters given in config and prepare an array of filters.
  var facets = [];

  const updateFilterResult = (itm) => {
    filterCounts[itm.attr] = itm.count;
    setfilters(filterCounts);
    if (typeof ref.current == 'object' && ref.current !== null) {
      const filters = ref.current.querySelectorAll('.c-collapse-item');
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

      props.callback({activeFilters, filterCounts, ...props});
    }
  }

  getFilters().forEach(facet => {
    facets.push(<WidgetManager key={facet.identifier} facet={facet} indexName={indexName} filterResult={(test) => updateFilterResult(test)} />);
  });

  return (
    <div ref={ref}>
      {facets}
    </div>
  );
}
