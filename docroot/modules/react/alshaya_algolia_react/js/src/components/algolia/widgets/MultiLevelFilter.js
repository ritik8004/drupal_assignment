import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// Seprate a string by space to get the attributes that were grouped.
// eg: we have brasize(32 A) = Bandsize(32), Cupsize(A) seprated by space.
// We will group all the cup sizes according to the bandsize to create a multilevel filter.
const MultiLevelFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, attribute, ...props
}) => {
  // Initially the count was updated when the filter
  // gets hide-facet-block class asynchronously,
  // due to which the filter was not appearing on page load.
  // The facet appeared when any other filter was getting applied.
  // for example: Sort By.
  // Now, the count for the filter is updated
  // once markup is available, so that on page load the filter is displayed
  // as the hide-facet-block class gets removed.
  if (typeof itemCount !== 'undefined') {
    setTimeout(() => {
      itemCount(props.attribute, items.length);
    }, 1);
  }

  // Do not show facets that have a single value if the render_single_result_facets is false.
  if (!drupalSettings.algoliaSearch.renderSingleResultFacets) {
    const exclude = drupalSettings.algoliaSearch.excludeRenderSingleResultFacets
      ? drupalSettings.algoliaSearch.excludeRenderSingleResultFacets.trim().split(',')
      : '';
    // Hide the filter if only one filter value available and not part of excluded list.
    if (exclude.length > 0) {
      if ((!exclude.includes(attribute.split('.')[0]) && items.length <= 1)) {
        return null;
      }
    } else if (items.length <= 1) {
      // Always hide the filter if attribute has single value
      // and exclude field doesn't have val.
      return null;
    }
  }
  // Create a multilevel array for grouping attr2 by attr1.
  // Eg: Cupsizes grouped by Bandsize.
  const attributesGroup = {};
  items.forEach((item) => {
    // eg: break Bra Size into Band and Cup Size (30 A => [30,A])
    const [attr1] = item.label.split(props.seprator);
    if (attributesGroup[attr1] === undefined) {
      attributesGroup[attr1] = [];
    }
    attributesGroup[attr1].push(item);
    attributesGroup[attr1].sort();
  });

  return (
    // Creating a multilevel dropdown.
    <ul className="block-facet--multi-level-widget__level-one">
      {Object.keys(attributesGroup).map((attr1) => (
        <li key={attr1} className="level-two">
          <h3 className="level-two__title">
            <span>{attr1}</span>
          </h3>
          <ul className="level-two__dropdown">
            {attributesGroup[attr1].map((item) => (
              <li
                key={item.label}
                className={`facet-item ${item.isRefined ? 'is-active' : ''}`}
                datadrupalfacetlabel={props.name}
              >
                <a
                  href="#"
                  onClick={(event) => {
                    event.preventDefault();
                    refine(item.value);
                  }}
                >
                  <span className="facet-item__value" data-drupal-facet-item-value={item.value}>
                    <span className="facet-item__label">{item.label.split(props.seprator)[1]}</span>
                    <span className="facet-item__count">
                      (
                      {item.count}
                      )
                    </span>
                  </span>
                </a>
              </li>
            ))}
          </ul>
        </li>
      ))}
    </ul>
  );
};

export default connectRefinementList(MultiLevelFilter);
