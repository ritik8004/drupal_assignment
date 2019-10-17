import React from 'react';
import { getPriceRangeLabel } from '../../utils';

const filtersLabels = {
  'attr_color_family.label': (value) =>  {
    const [label, ] = value.split(',');
    return label.trim();
  },
  'field_category_name.lvl0': (value) => {
    return value.replace('field_category_name.lvl0:', '').trim();
  },
  'attr_product_brand': (value) => {
    return value.trim();
  },
  'final_price': (value) => {
    const price = value.replace('final_price:', '').trim();
    return getPriceRangeLabel(price);
  },
  'attr_size': (value) => {
    return value;
  },
};

export default function FiltersLabels({ attribute, value }) {
  const label = filtersLabels[attribute](value);

  return (
    <React.Fragment>
      <span className="facet-item__status js-facet-deactivate">(-)</span>
      <span className="facet-item__value">{label}</span>
    </React.Fragment>
  );
}
