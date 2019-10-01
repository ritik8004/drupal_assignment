import React from 'react';
import { connectRefinementList } from 'react-instantsearch-dom';

const AlshayaRefinementList = ({ items, refine, searchForItems, isFromSearch, createURL }) => {

  console.log()

  const searchForm = (isFromSearch)
  ? (<li>
    <input
      type="search"
      onChange={event => searchForItems(event.currentTarget.value)}
    />
  </li>)
  : (null);

  return (
    <ul>
      {searchForm}
      {items.map(item => (
        <li key={item.label}>
          <a
            href={createURL(item.value)}
            style={{ fontWeight: item.isRefined ? 'bold' : '' }}
            onClick={event => {
              event.preventDefault();
              refine(item.value);
            }}>
            {item.label} ({item.count})
          </a>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(AlshayaRefinementList);
