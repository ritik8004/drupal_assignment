import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';
const HierarchicalMenu = ({ items, refine, createURL }) => (
  <ul>
    {items.map(item => (
      <li key={item.label}>
        <a
          href={`#${createURL(item.value)}`}
          className={"facet-item " + (item.isRefined ? 'is-active' : '')}
          onClick={event => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span className="facet-item__value">
            {item.label}
            <span className="facet-item__count">{`(${item.count})`}</span>
            </span>
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
