import React from 'react';
import FilterPanel from '../panels/FilterPanel';
import SortByList from '../algolia/widgets/SortByList';
import ColorFilter from '../algolia/widgets/ColorFilter';
import SizeGroupFilter from '../algolia/widgets/SizeGroupFilter';
import RefinementList from '../algolia/widgets/RefinementList';
import PriceFilter from '../algolia/widgets/PriceFilter';
import renderWidget from './RenderWidget';
import StarRatingFilter from '../algolia/widgets/StarRatingFilter';

const WidgetManager = React.memo((props) => {
  const
    {
      facet: filter, itemCount, facet: { name },
    } = props;

  let currentWidget = '';
  let className = '';
  switch (filter.widget.type) {
    case 'sort_by':
      currentWidget = (
        <SortByList
          name={name}
          defaultRefinement={filter.widget.items[0].value}
          items={filter.widget.items}
        />
      );
      break;

    case 'swatch_list':
      className = 'block-facet--swatch-list';
      currentWidget = (
        <ColorFilter
          name={name}
          facetValues={filter.facet_values}
          attribute={`${filter.identifier}.value`}
          searchable={false}
          itemCount={itemCount}
        />
      );
      break;

    case 'range_checkbox':
      currentWidget = (
        <PriceFilter
          name={name}
          attribute={filter.identifier}
          granularity={parseInt(filter.widget.config.granularity, 10)}
          itemCount={itemCount}
        />
      );
      break;

    case 'size_group_list':
      className = 'size_group_list';
      currentWidget = (
        <SizeGroupFilter
          name={name}
          attribute={filter.identifier}
          granularity={parseInt(filter.widget.config.granularity, 10)}
          itemCount={itemCount}
        />
      );
      break;

    case 'star_rating':
      currentWidget = (
        <StarRatingFilter
          name={name}
          attribute={filter.identifier}
          searchable={false}
          itemCount={itemCount}
        />
      );
      break;

    case 'checkbox':
    default:
      currentWidget = (
        <RefinementList
          name={name}
          attribute={filter.identifier}
          searchable={false}
          itemCount={itemCount}
        />
      );
  }

  return (
    <FilterPanel header={filter.label} id={filter.identifier} className={className}>
      {currentWidget}
    </FilterPanel>
  );
});

export default renderWidget(WidgetManager);
