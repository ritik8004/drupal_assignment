import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// StarRatingFilter used to display overall counts per star.
function StarRatingFilter(props) {
  const {
    items, attribute, refine, itemCount,
  } = props;

  if (typeof itemCount !== 'undefined') {
    itemCount(attribute, items.length);
  }
  const ratingItems = [];
  Object.entries(items).forEach(([key, values]) => {
    ratingItems[key] = values;
    const label = values.label.split('_');
    ratingItems[key].label = (label[1] > 1) ? `${label[1]} ${Drupal.t('stars')}` : `${label[1]} ${Drupal.t('star')}`;
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
            {item.label}
            <span className="facet-item__count">
              (
              {item.count}
              )
            </span>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(StarRatingFilter);
