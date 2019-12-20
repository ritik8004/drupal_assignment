import React from 'react';
import { connectSortBy } from 'react-instantsearch-dom';

const SortByList = ({ items, refine }) => (
  <ul>
    {items.map(item => (
      <li key={item.value} className={"facet-item " + (item.isRefined ? 'active-item' : '')}>
        <a
          href="#"
          onClick={event => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          {item.label}
        </a>
      </li>
    ))}
  </ul>
);

export default connectSortBy(SortByList);