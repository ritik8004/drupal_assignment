import React from 'react';
import FilterPanel from './FilterPanel';
import SortByList from './SortByList';
import ColorFilter from './widgets/ColorFilter';
import RefinementList from './widgets/RefinementList';
import PriceFilter from './widgets/PriceFilter';

console.log();

export default ({indexName}) => (
  <React.Fragment>
    <FilterPanel header="Sort By" id="sort_by">
      <SortByList
        defaultRefinement={indexName}
        items={drupalSettings.algoliaSearch.filters.sortby.widget.items}
      />
    </FilterPanel>
    <FilterPanel header="Price" id="final_price">
      <PriceFilter
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
      <RefinementList
        attribute="attr_product_brand"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Collection" id="attr_collection">
      <RefinementList
        attribute="attr_collection"
        searchable={false}
      />
    </FilterPanel>
    <FilterPanel header="Size" id="attr_size">
      <RefinementList
        attribute="attr_size"
        searchable={false}
      />
    </FilterPanel>
    <div className="show-all-filters">
      <span className="desktop">all filters</span>
      <span className="upto-desktop">filter &amp; sort</span>
    </div>
  </React.Fragment>
);
