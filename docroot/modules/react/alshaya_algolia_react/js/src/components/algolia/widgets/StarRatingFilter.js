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
    itemCount(attribute, items.length);
  }
  const ratingItems = [];
  const data = {};
  Object.entries(items).forEach(([key, values]) => {
    ratingItems[key] = values;
    const label = values.label.split('_');
    const star = label[1];
    ratingItems[key].label = (star > 1) ? `${star} ${Drupal.t('stars')}` : `${star} ${Drupal.t('star')}`;
    ratingItems[key].star = star;
    // Prepare dataset to build pretty path url.
    const value = values.value[0];
    if (values.value.length > 0) {
      data[value] = (star > 1) ? `${star}_stars` : `${star}_star`;
    }
  });

  // Store dataset in local storage to be used for pretty path.
  const facetName = attribute.replace('attr_', '');
  if (Object.entries(data).length > 0
    && (getFacetStorage(facetName) === null
    || Object.entries(getFacetStorage(facetName)).length === 0)) {
    setFacetStorage(facetName, data);
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
