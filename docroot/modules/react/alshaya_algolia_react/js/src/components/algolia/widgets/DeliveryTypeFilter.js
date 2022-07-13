import React, { useEffect, useState } from 'react';
import connectRefinementList from '../connectors/connectRefinementList';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { checkExpressDeliveryStatus, checkSameDayDeliveryStatus } from '../../../../../../js/utilities/expressDeliveryHelper';

const DeliveryTypeFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, ...props
}) => {
  // Set default state for express delivery state to show hide the facets.
  const [expressDeliveryFlag, setExpressDeliveryFlag] = useState([]);

  if (typeof itemCount !== 'undefined') {
    setTimeout(() => {
      itemCount(props.attribute, items.length);
    }, 1);
  }
  const deliveryItems = [];

  // Set express delivery flag with settings in event from MDC API for
  // express delivery settings.
  const expressDeliveryFacet = (e) => {
    const expressDeliveryStatus = e.detail;
    if (!expressDeliveryStatus) {
      setExpressDeliveryFlag(expressDeliveryStatus);
      setTimeout(() => {
        itemCount(props.attribute, items.length);
      }, 1);
    }
  };

  useEffect(() => {
    // This event is dispatched from expressDeliveryHelper through SearchApp
    // The event detail has response from API call to magento to get express
    // delivery settings to show hide delivery facet.
    document.addEventListener('expressDeliveryLabelsDisplay', expressDeliveryFacet, false);
    return () => {
      document.removeEventListener('expressDeliveryLabelsDisplay', expressDeliveryFacet, false);
    };
  }, []);

  // Return empty ul here if the express delivery flag is set to false from API
  // response.
  if (!expressDeliveryFlag) {
    return <ul />;
  }

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
        if (item.label === 'same_day_delivery_available' && !checkSameDayDeliveryStatus()) {
          return null;
        }
        if (item.label === 'express_day_delivery_available' && !checkExpressDeliveryStatus()) {
          return null;
        }

        if (item.label === 'same_day_delivery_available') {
          facetValues[item.label] = props.sameDayValue;
        } else if (item.label === 'express_day_delivery_available') {
          facetValues[item.label] = props.expressDeliveryValue;
        } else {
          facetValues[item.label] = item.label;
        }
        const [expressValue, expressClass] = facetValues[item.label].split(',');
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
