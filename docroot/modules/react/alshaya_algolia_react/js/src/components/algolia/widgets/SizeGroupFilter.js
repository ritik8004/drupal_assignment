import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// Creating size grouping filter.
const SizeGroupFilter = (
  {
    items, refine, itemCount, attribute, ...props
  },
) => {
  if (typeof itemCount !== 'undefined') {
    itemCount(attribute, items.length);
  }

  // Preparing sizes according to their groups.
  const groupedItems = [];
  Object.values(items).forEach((item) => {
    const data = item.label.split(':');
    if (groupedItems[data[0]] === undefined) {
      groupedItems[data[0]] = [];
    }

    groupedItems[data[0]].push(item);
  });

  // Moving other at the end of the size filter list.
  if (groupedItems['other']) {
    var otherVals = groupedItems['other'];
    delete groupedItems['other'];
    if (groupedItems['other'] === undefined) {
      var otherLabel = Drupal.t('other');
      groupedItems[otherLabel] = [];
      groupedItems[otherLabel] = otherVals;
    }
  }

  return (
    <ul>
      {Object.keys(groupedItems).map((group) => (
        <li key={group}>
          <span className="sizegroup-filter">{group}</span>
          <ul className="sizegroup" id={group}>
            {Object.values(groupedItems[group]).map((item) => (
              <li
                key={`${group}-${item.label.split(':').pop()}`}
                className={`facet-item  ${(item.isRefined ? 'is-active' : '')}`}
                datadrupalfacetlabel={props.name}
                onClick={(event) => {
                  event.preventDefault();
                  refine(item.value);
                }}
              >
                <span
                  className="facet-item__value"
                >
                  {item.label.split(':').pop().trim()}
                  <span className="facet-item__count">
                    (
                    {item.count}
                    )
                  </span>
                </span>
              </li>
            ))}
          </ul>
        </li>
      ))}
    </ul>
  );
};

export default connectRefinementList(SizeGroupFilter);
