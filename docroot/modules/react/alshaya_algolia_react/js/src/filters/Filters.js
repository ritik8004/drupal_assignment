import React from 'react';
import FilterPanel from './FilterPanel';
import SortByList from './SortByList';
import ColorFilter from './widgets/ColorFilter';
import RefinementList from './widgets/RefinementList';
import PriceFilter from './widgets/PriceFilter';
import { getFilters } from '../utils';

/**
 * Decide and return which widget to render based on Drupal widget types.
 *
 * @param {Array} filter
 *   The array of filter, with name, identifier and widget as key.
 * @param {String} indexName
 *   The current index name.
 */
function renderWidget(filter, indexName) {
  var currentWidget = '';
  var className = '';
  switch (filter.widget.type) {
    case 'sort_by':
      currentWidget = <SortByList defaultRefinement={indexName} items={filter.widget.items}/>;
      break;

    case 'swatch_list':
      className = 'block-facet--swatch-list';
      currentWidget = <ColorFilter attribute={`${filter.identifier}.label`} searchable={false} />;
      break;

    case 'range_checkbox':
      currentWidget = <PriceFilter attribute={filter.identifier} granularity={filter.widget.config.granularity} />;
      break;

    case 'checkbox':
    default:
      currentWidget = <RefinementList attribute={filter.identifier} searchable={false} />;
  }

  return (
    <FilterPanel header={filter.label} id={filter.identifier} className={className}>
      {currentWidget}
    </FilterPanel>
  )
}

export default ({indexName}) => {
  // Loop through all the filters given in config and prepare an array of filters.
  var facets = [];
  getFilters().forEach(facet => {
    facets.push(renderWidget(facet, indexName));
  });

  return (
    <React.Fragment>
      {facets}
    </React.Fragment>
  );
}
