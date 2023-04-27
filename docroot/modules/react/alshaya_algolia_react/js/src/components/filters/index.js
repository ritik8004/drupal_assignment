import React, { useState, useRef } from 'react';
import WidgetManager from '../widget-manager';
import DynamicWidgets from '../algolia/widgets/DynamicWidgets';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isConfigurableFiltersEnabled } from '../../../../../js/utilities/helper';
import { getFilters } from '../../utils';

const Filters = ({ indexName, pageType, ...props }) => {
  const [filterCounts, setfilters] = useState([]);
  const [facets, setFacets] = useState([]);
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

  /**
   * Builds Facets from userData received from algolia query response.
   *
   * @param {object} data
   *   Facets userData from algolia response.
   */
  const buildFacets = (data) => {
    if (!hasValue(data)) {
      return;
    }
    const { filters } = data[0];
    setFacets(Object.values(filters));
  };

  let facetsList = [];
  let facetsConfig = facets;

  if (!isConfigurableFiltersEnabled()) {
    // Check if configurable attributes is disabled.
    // Set facet config from drupal settings.
    facetsConfig = getFilters(pageType);
  }

  if (hasValue(facetsConfig)) {
    facetsConfig.forEach((facet) => {
      facetsList.push(
        <WidgetManager
          key={facet.identifier}
          facet={facet}
          indexName={indexName}
          filterResult={(test) => updateFilterResult(test)}
          pageType={pageType}
          attribute={facet.identifier}
        />,
      );
    });
  }

  // If configurable filters is enabled then wrap facetsLists in
  // Dynamic widgets component. Dynamic widgets component uses renderingContent
  // from algolia search result which has the facet display configuration.
  if (isConfigurableFiltersEnabled()) {
    facetsList = (
      <DynamicWidgets buildFacets={buildFacets}>
        {facetsList}
      </DynamicWidgets>
    );
  }

  return (
    <div ref={ref} className="filter-facets">
      {facetsList}
    </div>
  );
};

export default Filters;
