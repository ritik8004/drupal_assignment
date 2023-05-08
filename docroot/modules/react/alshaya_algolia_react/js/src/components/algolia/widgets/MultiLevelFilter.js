import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// Seprate a string by comma to get the label and color code/image/text.
const MultiLevelFilter = ({
  items, itemCount, refine, searchForItems, isFromSearch, attribute, ...props
}) => {
  let searchForm = (null);
  if (isFromSearch) {
    searchForm = (
      <li>
        <input
          type="search"
          onChange={(event) => searchForItems(event.currentTarget.value)}
        />
      </li>
    );
  }

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
    // Hide color filter if only one filter value available and not part of excluded list.
    if (exclude.length > 0) {
      if ((!exclude.includes(attribute) && items.length <= 1)) {
        return null;
      }
    } else if (items.length <= 1) {
      // Always hide color filter if attribute has single value and exclude field doesn't have val.
      return null;
    }
  }
  const braSizeGroup = {};
  items.forEach((item) => {
    const [bandSize] = item.label.split(' ');
    if (braSizeGroup[bandSize] === undefined) {
      braSizeGroup[bandSize] = [];
    }
    braSizeGroup[bandSize].push(item);
    braSizeGroup[bandSize].sort();
  });

  return (
    <ul>
      {searchForm}
      {Object.keys(braSizeGroup).map((bandSize) => (
        <li className="bra-size-group-title" key={bandSize}>
          {bandSize}
          <ul>
            {braSizeGroup[bandSize].map((item) => (
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
                    <span className="facet-item__label">{item.label.split(' ')[1]}</span>
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
