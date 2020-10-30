import React from 'react';
import { connectSortBy } from 'react-instantsearch-dom';

const SortByList = ({ items, refine, name }) => (
  <ul>
    {items.map((item) => (
      <li
        key={item.value}
        className={`facet-item ${item.isRefined ? 'active-item' : ''}`}
        datadrupalfacetlabel={name}
        onClick={(event) => {
          event.preventDefault();
          refine(item.value);
        }}
      >
        <a
          href="#"
          className="facet-item__value"
        >
          {item.label}
        </a>
      </li>
    ))}
  </ul>
);

export default connectSortBy(SortByList);
