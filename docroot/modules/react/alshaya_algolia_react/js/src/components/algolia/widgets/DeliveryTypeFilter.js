import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

const DeliveryTypeFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, ...props
}) => {
  if (typeof itemCount !== 'undefined') {
    setTimeout(() => {
      itemCount(props.attribute, items.length);
    }, 1);
  }
  const deliveryItems = [];
  Object.entries(items).forEach(([key, item]) => {
    deliveryItems[key] = item;
  });
  const { facetValues } = props;
  return (
    <ul>
      {deliveryItems.map((item) => {
        if (typeof facetValues[item.label] === 'undefined') {
          facetValues[item.label] = item.label;
        }
        const expressClass = facetValues[item.label].split(',')[1];
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
              <span className="facet-item__label">{item.label}</span>
              <span className="facet-item__count">{item.count}</span>
            </span>
          </li>
        );
      })}
    </ul>
  );
};

export default connectRefinementList(DeliveryTypeFilter);
