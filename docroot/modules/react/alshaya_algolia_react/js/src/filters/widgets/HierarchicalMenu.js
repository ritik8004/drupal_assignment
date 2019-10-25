import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';

const HierarchicalMenu = ({ items, refine, createURL }) => (
  <ul>
    {items.map(item => (
      <li key={item.label}>
        <a
          href={`#${createURL(item.value)}`}
          className={ item.isRefined ? 'active' : '' }
          onClick={event => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span class="ais-HierarchicalMenu-label">{item.label}</span>
          <span class="ais-HierarchicalMenu-count">{item.count}</span>
        </a>
        {item.items && (
          <HierarchicalMenu
            items={item.items}
            refine={refine}
            createURL={createURL}
          />
        )}
      </li>
    ))}
  </ul>
);

export default connectHierarchicalMenu(HierarchicalMenu);
