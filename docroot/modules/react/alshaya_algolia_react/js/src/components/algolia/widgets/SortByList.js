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
          // return if user selects selected sort option.
          if (item.isRefined) return;
          refine(item.value);
        }}
        data-sort={item.value}
        gtm-key={item.gtm_key}
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
