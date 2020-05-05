import React from 'react';
import { getPriceRangeLabel } from '../../../utils';

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
      selctionText = selctionSizeText.split(drupalSettings.algoliaSearch.sizeGroupSeparator).pop().trim();
      break;
    }

    case 'checkbox':
    default:
      selctionText = value.trim();
  }

  return selctionText;
}

export default function FiltersLabels({ attribute, value }) {
  const [attributeName] = attribute.split('.');
  const label = selectedFiltersLables(
    attribute,
    value,
    drupalSettings.algoliaSearch.filters[attributeName],
  );

  return (
    <>
      <span className="facet-item__status js-facet-deactivate">(-)</span>
      <span className="facet-item__value">{label}</span>
    </>
  );
}
