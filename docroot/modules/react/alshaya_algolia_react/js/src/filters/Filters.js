import React from 'react';
import FilterPanel from './FilterPanel';
import SortByList from './SortByList';
import ColorFilter from './widgets/ColorFilter';
import CommonRefinementList from './widgets/CommonRefinementList';
import PriceFilter from './widgets/PriceFilter';
import NewPriceFilter from './widgets/NewPriceFilter';

export default ({indexName}) => (
  <React.Fragment>
    <FilterPanel header="Sort By" id="sort_by">
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
    <FilterPanel header="Price" id="final_price">
      <NewPriceFilter
        attribute="final_price"
        granularity={5}
      />
    </FilterPanel>
    <FilterPanel header="Colour" id="attr_color_family" className="block-facet--swatch-list">
      <ColorFilter
        attribute="attr_color_family.label"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Brands" id="attr_product_brand">
      <CommonRefinementList
        attribute="attr_product_brand"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Collection" id="attr_collection">
      <CommonRefinementList
        attribute="attr_collection"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Size" id="attr_size">
      <CommonRefinementList
        attribute="attr_size"
        searchable={false}
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
    <div className="show-all-filters">
      <span className="desktop">all filters</span>
      <span className="upto-desktop">filter &amp; sort</span>
    </div>
  </React.Fragment>
);
