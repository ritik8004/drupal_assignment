import React from 'react';
import {
  HierarchicalMenu,
  Panel,
  RefinementList,
  SortBy,
  NumericMenu,
  CurrentRefinements
} from 'react-instantsearch-dom';
import { formatPrice } from '../components/price/PriceHelper';
import AlshayaRefinementList from '../widgets/AlshayaRefinementList';

export default ({indexName}) => (
  <div className="container-without-product">
    <Panel header="Sort By" className="c-facet c-accordion">
      <SortBy
        defaultRefinement={indexName}
        items={[
          { value: indexName, label: 'Featured' },
          { value: indexName + '_created_desc', label: 'New In.' },
          { value: indexName + '_title_asc', label: 'Name A to Z.' },
          { value: indexName + '_title_desc', label: 'Name Z to A.' },
          { value: indexName + '_final_price_desc', label: 'Price High to Low.' },
          { value: indexName + '_final_price_asc', label: 'Price Low to High.' },
        ]}
      />
    </Panel>
    <Panel header="Category" className="c-facet c-accordion">
      <HierarchicalMenu
        attributes={[
          'field_category_name.lvl0',
          'field_category_name.lvl1',
        ]}
      />
    </Panel>
    <Panel header="Brands" className="c-facet c-accordion">
      <RefinementList
        attribute="attr_product_brand"
        searchable={false}
      />
    </Panel>
    <Panel header="Colour" className="c-facet c-accordion">
      <RefinementList
        attribute="attr_color_family"
        searchable={false}
      />
    </Panel>
    <Panel header="Price" className="c-facet c-accordion">
      <NumericMenu
        attribute="final_price"
        createURL={() => '#'}
        refine={() => null}
        items={[
          { label: '<=' + formatPrice(10), end: 10, noRefinement: true },
          { label: formatPrice(10) + ' - ' + formatPrice(100), start: 10, end: 100, noRefinement: true },
          { label: formatPrice(100) + ' - ' + formatPrice(500), start: 100, end: 500, noRefinement: true },
          { label: '>= ' + formatPrice(500), start: 500, noRefinement: true },
        ]}
        canRefine={true}
      />
    </Panel>
    <Panel header="Size" className="c-facet c-accordion">
      <RefinementList
        attribute="attr_size"
        searchable={false}
      />
    </Panel>
  </div>
);