import React from 'react';
import { connectRefinementList } from 'react-instantsearch-dom';
import Swatch from './Swatch';

// Seprate a string by comma to get the label and color code/image/text.
const ColorFilter = ({ items, refine, searchForItems, isFromSearch, createURL }) => {
  var searchForm = (null);
  if (isFromSearch) {
    searchForm = (
      <li>
        <input
          type="search"
          onChange={event => searchForItems(event.currentTarget.value)}
        />
      </li>
    );
  }

  return (
    <ul>
      {searchForm}
      {items.map(item => {
        const [label, swatch_info] = item.label.split(',');
        return (
          <li key={item.label} className={"facet-item " + (item.isRefined ? 'is-active' : '')}>
            <a
              href="#"
              onClick={(event) => {
                event.preventDefault();
                refine(item.value);
              }}
            >
              <Swatch label={label} swatch={swatch_info} />
              <span className="facet-item__value">{label}
                <span className="facet-item__count">({item.count})</span>
              </span>
            </a>
          </li>
        );
      })}
    </ul>
  );
}

export default connectRefinementList(ColorFilter);
