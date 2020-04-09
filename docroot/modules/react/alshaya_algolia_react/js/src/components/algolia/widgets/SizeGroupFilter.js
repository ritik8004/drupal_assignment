import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// Creating size grouping filter.
const SizeGroupFilter = ({items, refine, itemCount, attribute, ...props}) => {
  if (typeof itemCount != 'undefined') {
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

  return (
    <ul>
      {Object.keys(groupedItems).map((group) => {
        return (
          <li key={group}>
            <span className="sizegroup-filter">{group}</span>
            <ul className="sizegroup" id={group}>
              {Object.values(groupedItems[group]).map((item) => {
                return (
                  <li
                    key={group + "-" + item.label.split(":").pop()}
                    className={"facet-item " + (item.isRefined ? 'is-active' : '')}
                    datadrupalfacetlabel={props.name}
                    onClick={event => {
                      event.preventDefault();
                      refine(item.value);
                    }}
                  >
                    <span
                      className="facet-item__value">{item.label.split(":").pop().trim()}
                      <span className="facet-item__count">({item.count})</span>
                    </span>
                  </li>
                );
              })}
            </ul>
          </li>
        )
      })}
    </ul>
  );
};

export default connectRefinementList(SizeGroupFilter);
