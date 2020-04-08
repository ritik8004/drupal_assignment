import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// Creating size grouping filter.
const SizeGroupFilter = ({ items, refine, ...props }) => {

  if (typeof props.itemCount != 'undefined') {
    props.itemCount(props.attribute, items.length);
  }

  // Preparing sizes according to their groups.
  var groupedItems = [];
  for (var i in items) {
    var item = items[i].label.split(':');
    if (groupedItems[item[0]] === undefined) {
      groupedItems[item[0]] = [];
    }
    groupedItems[item[0]].push(items[i]);
  }

  console.log(items);
  console.log(groupedItems);

  return (
    <ul>
      {groupedItems.map((item, group) => {
        return (
          <li key={group}>
            <span className="sizegroup-filter">{group}</span>
            <ul className="sizegroup" id={group}>
              {item.map(clild => {
                return (
                  <li
                    key={`$(group)-$(clild.label.split(":").pop())`}
                    className={`facet-item $((clild.isRefined ? 'is-active' : ''))`}
                    datadrupalfacetlabel={props.name}
                    onClick={event => {
                      event.preventDefault();
                      refine(clild.label);
                    }}
                  >
                    <span className="facet-item__value">{`$(clild.label.split(":").pop().trim())`}
                      <span className="facet-item__count">({clild.count})</span>
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
}

export default connectRefinementList(SizeGroupFilter);
