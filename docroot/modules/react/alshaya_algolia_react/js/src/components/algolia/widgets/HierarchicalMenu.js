import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';

const HierarchicalMenu = (props) => {
  const { items, refine, createURL } = props;

  // Setting flag to set 'All' filter to active
  // when no other filters are selected.
  function noActiveFilters(items) {
    for (let elem of items) {
        if (elem.isRefined) {
          return false;
        }
    }
    return true
  }

  return (
    <ul>
      {items.map(item => (
        <li key={item.label}>
          <a
            href={`#${createURL(item.value)}`}
            className={
              (item.value === window.Drupal.t('All')? "facet-item " + (noActiveFilters(items) ?
              'is-active category-all' : '') : "facet-item " + (item.isRefined ? 'is-active' : ''))
            }
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
          {item.items && item.items.length > 0 && (
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
