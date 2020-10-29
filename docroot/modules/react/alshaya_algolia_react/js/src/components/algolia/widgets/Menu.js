import React from 'react';
import { connectMenu } from 'react-instantsearch-dom';

const Menu = (props) => {
  const { items, refine, createURL } = props;

  // Setting flag to set 'All' filter to active
  // when no other filters are selected.
  function noActiveFilters(itemsFilter) {
    for (let i = 0; i < itemsFilter.length; i++) {
      if (itemsFilter[i].isRefined) {
        return false;
      }
    }
    return true;
  }

  return (
    <ul>
      {items.map((item) => (
        <li key={item.label}>
          <a
            href={`#${createURL(item.value)}`}
            className={
              (item.value === window.Drupal.t('All') ? `facet-item ${noActiveFilters(items)
                ? 'is-active super-category-all' : ''}` : `facet-item ${item.isRefined ? 'is-active ' : ''}`)
            }
            onClick={(event) => {
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
        </li>
      ))}
    </ul>
  );
};

export default connectMenu(Menu);
