import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';
// import SwatchList from './SwatchList';

// Creating size grouping filter.
const SizeGroupFilter = ({ items, refine, searchForItems, isFromSearch, ...props }) => {
  var searchForm = (null);
  if (isFromSearch) {
    searchForm = (
      <li>
        <input
          type="search"
          onChange={event => searchForItems(event.currentTarget.value)}
        />
      </li>
    );
  }

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

  return (
    <ul>
      {searchForm}
      {groupedItems.map((item, group) => {
        return (
          <li>
            <ul class="sizegroup">
              <span className="sizegroup-filter">{ group }</span>
              {item.map(clild => {
                return (
                  <li
                    key={clild.label}
                    className={"facet-item " + (clild.isRefined ? 'is-active' : '')}
                    datadrupalfacetlabel={props.name}
                    onClick={event => {
                      event.preventDefault();
                      refine(clild.label);
                    }}
                  >
                    <span className="facet-item__value">{clild.label.split(":").pop()}
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
