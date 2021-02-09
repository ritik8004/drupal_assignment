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
    itemCount(props.attribute, items.length);
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
              <span className="facet-item__value">
                {label}
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
