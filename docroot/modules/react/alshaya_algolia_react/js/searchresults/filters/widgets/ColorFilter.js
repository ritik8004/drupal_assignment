import React from 'react';
import { connectRefinementList } from 'react-instantsearch-dom';
import Swatch from './Swatch';

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
          <li key={item.label}>
            <a
              href="#"
              style={{ fontWeight: item.isRefined ? 'bold' : '' }}
              onClick={(event) => {
                event.preventDefault();
                refine(item.value);
              }}
            >
              <Swatch label={label} swatch={swatch_info} />
              <span class="facet-item__value">{label}
                <span class="facet-item__count">({item.count})</span>
              </span>
            </a>
          </li>
        );
      })}
    </ul>
  );
}

export default connectRefinementList(ColorFilter);
