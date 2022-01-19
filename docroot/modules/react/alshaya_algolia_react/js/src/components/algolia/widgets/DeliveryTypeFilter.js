import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { checkExpressDeliveryStatus, checkSameDayDeliveryStatus } from '../../../../../../js/utilities/expressDeliveryHelper';

const DeliveryTypeFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, ...props
}) => {
  if (typeof itemCount !== 'undefined') {
    setTimeout(() => {
      itemCount(props.attribute, items.length);
    }, 1);
  }
  const deliveryItems = [];
  if (!hasValue(items)) {
    return <ul />;
  }
  Object.entries(items).forEach(([key, item]) => {
    deliveryItems[key] = item;
  });
  const { facetValues } = props;
  if (!hasValue(deliveryItems)) {
    return <ul />;
  }
  return (
    <ul>
      {deliveryItems.map((item) => {
        if (typeof facetValues[item.label] === 'undefined') {
          facetValues[item.label] = item.label;
        }
        const [expressValue, expressClass] = facetValues[item.label].split(',');
        if (item.label === 'same_day_delivery_available' && !checkSameDayDeliveryStatus()) {
          return null;
        }
        if (item.label === 'express_day_delivery_available' && !checkExpressDeliveryStatus()) {
          return null;
        }
        return (
          <li
            key={item.label}
            className={`facet-item ${expressClass} ${item.isRefined ? 'is-active' : ''}`}
            datadrupalfacetlabel={props.name}
            onClick={(event) => {
              event.preventDefault();
              refine(item.value);
            }}
          >
            <span className="facet-item__value" data-drupal-facet-item-value={item.value}>
              <span className="facet-item__label">{expressValue}</span>
              <span className="facet-item__count">
                (
                {item.count}
                )
              </span>
            </span>
          </li>
        );
      })}
    </ul>
  );
};

export default connectRefinementList(DeliveryTypeFilter);
