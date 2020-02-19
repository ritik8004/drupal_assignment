import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';

const HierarchicalMenu = (props) => {
  const { items, refine, createURL } = props;

  return (
    <ul>
      {items.map(item => (
        <li key={item.label}>
          <a
            href={`#${createURL(item.value)}`}
            className={"facet-item " + (item.isRefined ? 'is-active' : '')}
            onClick={event => {
              event.preventDefault();
              if (item.value === window.Drupal.t('All')) {
                refine(null);
                return;
              }

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
              sortResults={false}
              items={item.items}
              refine={refine}
              createURL={createURL}
            />
          )}
        </li>
      ))}
    </ul>
  );
};

export default connectHierarchicalMenu(HierarchicalMenu);
