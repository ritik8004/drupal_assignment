import React from 'react';
import { connectRefinementList } from 'react-instantsearch-dom';

function CommonRefinement({ items, isFromSearch, refine, searchForItems, createURL }) {
  return (
    <ul>
      {items.map(item => (
        <li key={item.label}>
          <a
            href={createURL(item.value)}
            style={{ fontWeight: item.isRefined ? 'bold' : '' }}
            onClick={event => {
              event.preventDefault();
              refine(item.value);
            }}
          >
            <label for="size-0-2M">
              <span class="facet-item__value">
                {item.label}
                <span class="facet-item__count">({item.count})</span>
              </span>
            </label>
          </a>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(CommonRefinement);
