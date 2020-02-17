import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';

const HierarchicalMenu = ({ items, refine, createURL }) => (
  <ul>
    {items.map(item => (
      <li key={item.label} className={ item.isRefined ? 'active' : '' }>
        <a
          href={`#${createURL(item.value)}`}
          onClick={event => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span className="ais-HierarchicalMenu-label">{item.label}</span>
          <span className="ais-HierarchicalMenu-count">{item.count}</span>
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
