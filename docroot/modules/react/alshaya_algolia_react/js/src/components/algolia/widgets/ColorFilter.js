import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';
import SwatchList from './SwatchList';

// Seprate a string by comma to get the label and color code/image/text.
const ColorFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, ...props
}) => {
  let searchForm = (null);
  if (isFromSearch) {
    searchForm = (
      <li>
        <input
          type="search"
          onChange={(event) => searchForItems(event.currentTarget.value)}
        />
      </li>
    );
  }

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

  const { facetValues } = props;

  return (
    <ul>
      {searchForm}
      {items.map((item) => {
        if (typeof facetValues[item.label] === 'undefined') {
          facetValues[item.label] = item.label;
        }

        const [label, swatchInfo] = facetValues[item.label].split(',');
        return (
          <li
            key={item.label}
            className={`facet-item ${item.isRefined ? 'is-active' : ''}`}
            datadrupalfacetlabel={props.name}
          >
            <a
              href="#"
              onClick={(event) => {
                event.preventDefault();
                refine(item.value);
              }}
            >
              <SwatchList label={label} swatch={swatchInfo} />
              <span className="facet-item__value" data-drupal-facet-item-value={label}>
                <span className="facet-item__label">{label}</span>
                <span className="facet-item__count">
                  (
                  {item.count}
                  )
                </span>
              </span>
            </a>
          </li>
        );
      })}
    </ul>
  );
};

export default connectRefinementList(ColorFilter);
