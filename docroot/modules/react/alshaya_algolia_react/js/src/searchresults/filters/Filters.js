import React from 'react';
import {
  HierarchicalMenu,
  Panel,
  RefinementList,
  SortBy,
  NumericMenu
} from 'react-instantsearch-dom';
import { formatPrice } from '../../components/price/PriceHelper';
import ColorFilter from './widgets/ColorFilter';
import CommonRefinementList from './widgets/CommonRefinementList';
import PriceFilter from './widgets/PriceFilter';
import FilterPanel from './FilterPanel';
import SortByList from '../SortByList';

export default ({indexName}) => (
  <div className="container-without-product">
    <FilterPanel header="Sort By">
      <SortByList
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
    </FilterPanel>
    {/* <Panel header="Category" className="c-facet c-accordion">
      <HierarchicalMenu
        attributes={[
          'field_category_name.lvl0',
          'field_category_name.lvl1',
          'field_category_name.lvl2',
        ]}
      />
    </Panel> */}
    <FilterPanel header="Brands">
      <CommonRefinementList
        attribute="attr_product_brand"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Colour" className="block-facet--swatch-list">
      <ColorFilter
        attribute="attr_color_family.label"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Price">
      <PriceFilter
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
    </FilterPanel>
    <FilterPanel header="Size">
      <CommonRefinementList
        attribute="attr_size"
        searchable={false}
      />
    </FilterPanel>
  </div>
);
