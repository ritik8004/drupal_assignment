import React from 'react';
import DisplayStar from '../../stars';
import connectRefinementList from '../connectors/connectRefinementList';
import { getFacetStorage, setFacetStorage } from '../../../utils/requests';

// StarRatingFilter used to display overall counts per star.
function StarRatingFilter(props) {
  const {
    items, attribute, refine, itemCount,
  } = props;

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
  const ratingItems = [];
  const data = {};
  const { currentLanguage } = drupalSettings.path;
  Object.entries(items).forEach(([key, item]) => {
    ratingItems[key] = item;
    const label = item.label.split('_');
    const star = label[1];
    ratingItems[key].label = (star > 1) ? `${star} ${Drupal.t('stars')}` : `${star} ${Drupal.t('star')}`;
    ratingItems[key].star = star;
    // Prepare dataset to build pretty path url.
    const value = item.value[0];
    if (item.value.length > 0) {
      data[value] = (star > 1) ? `${star}_stars` : `${star}_star`;
    }
  });

  // Sort filter values by top ratings.
  ratingItems.sort((a, b) => (b.star - a.star));

  // Store dataset in local storage to be used for pretty path.
  let facetName = attribute.replace('attr_', '');
  if (facetName.includes(`.${currentLanguage}`)) {
    facetName = facetName.replace(`.${currentLanguage}`, '');
  }

  if (Object.entries(data).length > 0
    && (getFacetStorage(facetName) === null
    || Object.entries(getFacetStorage(facetName)).length === 0)) {
    setFacetStorage(facetName, data);
  }

  // Do not show facets that have a single value if the render_single_result_facets is false.
  if (!drupalSettings.algoliaSearch.renderSingleResultFacets) {
    const exclude = drupalSettings.algoliaSearch.excludeRenderSingleResultFacets
      ? drupalSettings.algoliaSearch.excludeRenderSingleResultFacets.trim().split(',')
      : '';
    // Certain factes should always be rendered irrespective of render_single_result_facets.
    // So we only consider the attributes not part of the exclude_render_single_result_facets.
    if (exclude.length > 0) {
      if ((!exclude.includes(attribute.split('.')[0]) && ratingItems.length <= 1)) {
        return null;
      }
    } else if (ratingItems.length <= 1) {
      return null;
    }
  }

  return (
    <ul>
      {ratingItems.map((item) => (
        <li
          key={item.label}
          className={`facet-item ${item.isRefined ? 'is-active' : ''}`}
          datadrupalfacetlabel={props.name}
          onClick={() => {
            refine(item.value);
          }}
        >
          <span className="facet-item__value">
            <div className="listing-inline-star">
              <div className="rating-label">{item.label}</div>
              <DisplayStar starPercentage={item.star} />
              <span className="facet-item__count">
                (
                {item.count}
                )
              </span>
            </div>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(StarRatingFilter);
