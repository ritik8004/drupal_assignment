import React from 'react';
import DisplayStar from '../../stars';
import connectRefinementList from '../connectors/connectRefinementList';

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
  Object.entries(items).forEach(([key, values]) => {
    ratingItems[key] = values;
    const label = values.label.split('_');
    const star = label[1];
    ratingItems[key].label = (star > 1) ? `${star} ${Drupal.t('stars')}` : `${star} ${Drupal.t('star')}`;
    ratingItems[key].star = star;
  });

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
