import React from 'react';
import { getPriceRangeLabel } from '../../../utils';
import { productListIndexStatus } from '../../../utils/indexUtils';

/**
 * Format selected values to make it presentable for selected filters.
 *
 * @param {String} attribute
 *   The selected attribute.
 * @param {String} value
 *   The value of selected attribute.
 * @param {Array} filter
 *   The widget array of facet.
 */
function selectedFiltersLables(attribute, value, filter) {
  let selctionText = '';
  let selctionSizeText;
  switch (filter.widget.type) {
    case 'swatch_list': {
      const [label] = value.split(',');
      selctionText = label.trim();
      break;
    }

    case 'menu':
    case 'hierarchy':
      selctionText = value.replace(`${attribute}:`, '').trim();
      break;

    case 'range_checkbox': {
      const price = value.replace(`${attribute}:`, '').trim();
      selctionText = getPriceRangeLabel(price);
      break;
    }

    case 'size_group_list': {
      selctionSizeText = value.replace(`${attribute}:`, '');
      selctionText = selctionSizeText.split(
        drupalSettings.algoliaSearch.sizeGroupSeparator,
      ).pop().trim();
      break;
    }

    case 'star_rating': {
      const selctionVal = value.replace('rating_', '');
      selctionText = (selctionVal > 1)
        ? `${selctionVal} ${Drupal.t('stars')}` : `${selctionVal} ${Drupal.t('star')}`;
      break;
    }

    case 'checkbox':
    default:
      selctionText = value.trim();
  }

  return selctionText;
}

export default function FiltersLabels({ attribute, value, pageType = null }) {
  const [attributeName] = attribute.split('.');
  const name = (attributeName === 'lhn_category') ? 'field_category' : attributeName;
  let settings = drupalSettings.algoliaSearch.search.filters[name];
  if (pageType === 'plp' && productListIndexStatus()) {
    settings = drupalSettings.algoliaSearch.listing.filters[name];
  }
  const label = selectedFiltersLables(
    attribute,
    value,
    settings,
  );

  return (
    <>
      <span className="facet-item__status js-facet-deactivate">(-)</span>
      <span className="facet-item__value">{label}</span>
    </>
  );
}
