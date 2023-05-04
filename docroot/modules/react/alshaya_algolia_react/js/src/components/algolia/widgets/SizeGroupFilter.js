import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// Creating size grouping filter.
const SizeGroupFilter = (
  {
    items, refine, itemCount, attribute, ...props
  },
) => {
  if (typeof itemCount !== 'undefined') {
    // Initially the count was updated when the filter
    // gets hide-facet-block class asynchronously,
    // due to which the filter was not appearing on page load.
    // The facet appeared when any other filter was getting applied.
    // for example: Sort By.
    // Now, the count for the filter is updated
    // once markup is available, so that on page load the filter is displayed
    // as the hide-facet-block class gets removed.
    setTimeout(() => {
      itemCount(attribute, items.length);
    }, 1);
  }

  // Preparing sizes according to their groups.
  const groupedItems = [];
  Object.values(items).forEach((item) => {
    const data = item.label.split(drupalSettings.algoliaSearch.sizeGroupSeparator);
    if (groupedItems[data[0]] === undefined) {
      groupedItems[data[0]] = [];
    }

    groupedItems[data[0]].push(item);
  });

  // Moving other at the end of the size filter list.
  if (groupedItems.other) {
    const otherVals = groupedItems.other;
    delete groupedItems.other;
    if (groupedItems.other === undefined) {
      const otherLabel = Drupal.t('other');
      groupedItems[otherLabel] = [];
      groupedItems[otherLabel] = otherVals;
    }
  }
  // Do not show facets that have a single value if the render_single_result_facets is false.
  if (!drupalSettings.algoliaSearch.renderSingleResultFacets) {
    const exclude = drupalSettings.algoliaSearch.excludeRenderSingleResultFacets
      ? drupalSettings.algoliaSearch.excludeRenderSingleResultFacets.trim().split(',')
      : '';
    // Certain factes should always be rendered irrespective of render_single_result_facets.
    // So we only consider the attributes not part of the exclude_render_single_result_facets.
    if (exclude.length > 0) {
      // Split attribute because attribute contain language suffix on PLP.
      if ((!exclude.includes(attribute.split('.')[0]) && items.length <= 1)) {
        return null;
      }
    } else if (items.length <= 1) {
      return null;
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
                key={`${group}-${item.label.split(drupalSettings.algoliaSearch.sizeGroupSeparator).pop()}`}
                className={`facet-item  ${(item.isRefined ? 'is-active' : '')}`}
                datadrupalfacetlabel={props.name}
                onClick={(event) => {
                  event.preventDefault();
                  refine(item.value);
                }}
              >
                <span className="facet-item__value" data-drupal-facet-item-value={item.label.split(drupalSettings.algoliaSearch.sizeGroupSeparator).pop().trim()}>
                  <span className="facet-item__label">{item.label.split(drupalSettings.algoliaSearch.sizeGroupSeparator).pop().trim()}</span>
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
