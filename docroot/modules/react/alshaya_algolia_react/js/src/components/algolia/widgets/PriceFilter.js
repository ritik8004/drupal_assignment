import React from 'react';
import connectWithPriceFilter from '../connectors/connectWithPriceFilter';
import { getPriceRangeLabel, isFacetsOnlyHasSingleValue } from '../../../utils';

const PriceFilter = (props) => {
  const {
    items, itemCount, refine, attribute,
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
      itemCount(props.attribute, items.length);
    }, 1);
  }
  // Do not show facets that have a single value if the render_single_result_facets is false.
  // hide facet if has single value.
  const singleValue = isFacetsOnlyHasSingleValue(attribute, items);
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
          onClick={(event) => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span className="facet-item__value" data-drupal-facet-item-value={getPriceRangeLabel(item.label)}>
            <span className="facet-item__label">{getPriceRangeLabel(item.label)}</span>
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

export default connectWithPriceFilter(PriceFilter);
