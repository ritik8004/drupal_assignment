import React, { useEffect } from 'react';
import connectRefinementList from '../connectors/connectRefinementList';
import { hasSingleValue } from '../../../utils';

// RefinementList used commonly for most of filters.
const CommonRefinement = (props) => {
  const {
    items, attribute, refine, itemCount,
  } = props;

  if (typeof itemCount !== 'undefined') {
    // Initially the count was updated when the filter
    // gets hide-facet-block class asynchronously,
    // due to which the filter was not appearing on page load.
    // The facet appeared when any other filter was getting applied.
    // for example: Sort By.
    // Now, the count for the filter is updated
    // once markup is available, so that on page load the filter is displayed
    // as the hide-facet-block class gets removed.
    setTimeout(() => {
      itemCount(attribute, items.length);
    }, 1);
  }

  useEffect(() => {
    // Refreshing any DOM changes dependant on presence of data.
    const eventAlgoliaRefinementListUpdated = new CustomEvent('algoliaRefinementListUpdated', { bubbles: true, detail: { attribute, items } });
    document.dispatchEvent(eventAlgoliaRefinementListUpdated);
  });
  // Do not show facets that have a single value if the render_single_result_facets is false.
  // hide facet if has single value.
  const singleValue = hasSingleValue(attribute, items);
  if (singleValue === true) {
    return null;
  }

  return (
    <ul>
      {items.map((item) => (
        <li
          key={item.label}
          className={`facet-item ${item.isRefined ? 'is-active' : ''}`}
          datadrupalfacetlabel={props.name}
          onClick={() => {
            refine(item.value);
          }}
        >
          {/* <label for={`${attribute}-${item.label}`}> */}
          <span className="facet-item__value" data-drupal-facet-item-value={item.label}>
            <span className="facet-item__label">{item.label}</span>
            <span className="facet-item__count">
              (
              {item.count}
              )
            </span>
          </span>
        </li>
      ))}
    </ul>
  );
};

export default connectRefinementList(CommonRefinement);
