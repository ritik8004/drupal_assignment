import React from 'react';
import FilterPanel from '../panels/FilterPanel';
import SortByList from '../algolia/widgets/SortByList';
import ColorFilter from '../algolia/widgets/ColorFilter';
import RefinementList from '../algolia/widgets/RefinementList';
import PriceFilter from '../algolia/widgets/PriceFilter';
import renderWidget from './RenderWidget';

const WidgetManager = React.memo((props) => {
  const {facet: filter, indexName, itemCount, facet: { name }}  = props;

  var currentWidget = '';
  var className = '';
  switch (filter.widget.type) {
    case 'sort_by':
      currentWidget = <SortByList defaultRefinement={indexName} items={filter.widget.items}/>;
      break;

    case 'swatch_list':
      className = 'block-facet--swatch-list';
      currentWidget = <ColorFilter name={name} attribute={`${filter.identifier}.label`} searchable={false} itemCount={itemCount} deditems={filter.widget.items} />;
      break;

    case 'range_checkbox':
      currentWidget = <PriceFilter name={name} attribute={filter.identifier} granularity={parseInt(filter.widget.config.granularity)} itemCount={itemCount} />;
      break;

    case 'checkbox':
    default:
      currentWidget = <RefinementList name={name} attribute={filter.identifier} searchable={false} itemCount={itemCount} />;
  }

  return (
    <FilterPanel header={filter.label} id={filter.identifier} className={className}>
      {currentWidget}
    </FilterPanel>
  );
});

export default renderWidget(WidgetManager);
