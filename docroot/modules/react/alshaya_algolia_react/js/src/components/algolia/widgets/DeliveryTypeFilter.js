import React, { useEffect, useState } from 'react';
import connectRefinementList from '../connectors/connectRefinementList';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import { checkExpressDeliveryStatus, checkSameDayDeliveryStatus } from '../../../../../../js/utilities/expressDeliveryHelper';
import { isFacetsOnlyHasSingleValue } from '../../../utils';

const DeliveryTypeFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, attribute, ...props
}) => {
  // Set default state for express delivery state to show hide the facets.
  const [expressDeliveryFlag, setExpressDeliveryFlag] = useState(window.sddEdStatus);

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
    if (expressDeliveryStatus) {
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

  if (!hasValue(items)) {
    return <ul />;
  }
  Object.entries(items).forEach(([key, item]) => {
    // Show sdd / ed facet filter if configuration is not received from
    // magento from an event.
    if (typeof expressDeliveryFlag === 'undefined') {
      deliveryItems[key] = item;
    }
    // Check if express delivery is enabled on magento configuration and show the filter.
    if (item.label === 'express_day_delivery_available' && expressDeliveryFlag.expressDelivery) {
      deliveryItems[key] = item;
    }
    // Check if same day delivery is enabled on magento configuration and show the filter.
    if (item.label === 'same_day_delivery_available' && expressDeliveryFlag.sameDayDelivery) {
      deliveryItems[key] = item;
    }
  });
  const { facetValues } = props;
  if (!hasValue(deliveryItems)) {
    return <ul />;
  }
  // Do not show facets that have a single value if the render_single_result_facets is false.
  // hide facet if has single value.
  const options = [];
  if (checkSameDayDeliveryStatus()) {
    options.push('same_day_delivery_available');
  }
  if (checkExpressDeliveryStatus()) {
    options.push('express_day_delivery_available');
  }
  const singleValue = isFacetsOnlyHasSingleValue(attribute, options);
  if (singleValue === true) {
    return null;
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
