import React from 'react';
import { connectHierarchicalMenu } from 'react-instantsearch-dom';

const HierarchicalMenu = (props) => {
  const { items, refine, createURL } = props;
  let sortedItems = {};

  if (props.sortResults === true && items !== null && items.length > 0) {
    let weight = {};
    let l1MenuItems = document.getElementsByClassName('menu--one__link');
    for (let i in l1MenuItems) {
      try {
        if (l1MenuItems[i].getAttribute('title') !== null) {
          // Add 10 to allow adding All at top.
          weight[l1MenuItems[i].getAttribute('title')] = parseInt(i) + 10;
        }
      }
      catch (e) {
      }
    }


    for (let i in items) {
      if (weight[items[i].label] !== undefined) {
        sortedItems[weight[items[i].label]] = items[i];
      }
      else if (items[i].label === window.Drupal.t('All')) {
        // Use 1 for All to ensure Object.values work properly.
        sortedItems[1] = items[i];
      }
    }

    sortedItems = Object.values(Object.keys(sortedItems).reduce((a, c) => (a[c] = sortedItems[c], a), {}));
  }
  else {
    sortedItems = items;
  }

  return (
    <ul>
      {sortedItems.map(item => (
        <li key={item.label} className={ item.isRefined ? 'active' : '' } >
          <a
            href={`#${createURL(item.value)}`}
            onClick={event => {
              event.preventDefault();
              if (item.value === window.Drupal.t('All')) {
                refine(null);
                return;
              }

              refine(item.value);
            }}
          >
            <span className="ais-HierarchicalMenu-label">{item.label}</span>
            <span className="ais-HierarchicalMenu-count">{item.count}</span>
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
