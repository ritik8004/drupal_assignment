import React from 'react';
import FilterPanel from '../panels/FilterPanel';
import SortByList from '../algolia/widgets/SortByList';
import ColorFilter from '../algolia/widgets/ColorFilter';
import SizeGroupFilter from '../algolia/widgets/SizeGroupFilter';
import RefinementList from '../algolia/widgets/RefinementList';
import PriceFilter from '../algolia/widgets/PriceFilter';
import renderWidget from './RenderWidget';
import StarRatingFilter from '../algolia/widgets/StarRatingFilter';
import DeliveryTypeFilter from '../algolia/widgets/DeliveryTypeFilter';
import ConditionalView from '../../../common/components/conditional-view';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import { getBackToPlpPageIndex } from '../../utils/indexUtils';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isConfigurableFiltersEnabled } from '../../../../../js/utilities/helper';

const WidgetManager = React.memo((props) => {
  const
    {
      facet: filter, itemCount, facet: { name }, pageType,
    } = props;

  let currentWidget = '';
  let className = '';
  let plpSortIndex = null;

  switch (filter.widget.type) {
    case 'sort_by':
      // If page type is search then default sort index is taken from filter.
      if (pageType !== 'search') {
        plpSortIndex = getBackToPlpPageIndex();
      }
      currentWidget = (
        <SortByList
          name={name}
          defaultRefinement={plpSortIndex || filter.widget.items[0].value}
          items={filter.widget.items}
        />
      );
      break;

    case 'swatch_list':
      className = 'block-facet--swatch-list';
      currentWidget = (
        <ColorFilter
          name={name}
          filterConfig={filter}
          attribute={`${filter.identifier}.value`}
          searchable={false}
          itemCount={itemCount}
          swatch
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

    case 'delivery_ways': {
      let sameDayValue = hasValue(filter.same_value) ? filter.same_value : '';
      let expressDeliveryValue = hasValue(filter.express_value) ? filter.express_value : '';

      // If configurable filters is enabled then prepare label values from
      // facet values.
      if (isConfigurableFiltersEnabled()) {
        const {
          same_day_delivery_available: sameValue,
          express_day_delivery_available: expressValue,
        } = filter.facet_values;
        sameDayValue = sameValue;
        expressDeliveryValue = expressValue;
      }

      currentWidget = (
        <ConditionalView condition={
          isExpressDeliveryEnabled()
        }
        >
          <DeliveryTypeFilter
            name={name}
            facetValues={filter.facet_values}
            attribute={filter.identifier}
            itemCount={itemCount}
            sameDayValue={sameDayValue}
            expressDeliveryValue={expressDeliveryValue}
          />
        </ConditionalView>
      );
      break;
    }
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
