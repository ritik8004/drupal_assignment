import React from 'react';
import { InstantSearch, Configure } from 'react-instantsearch-dom';
import { searchClient } from '../../../../js/utilities/algoliaHelper';
import Menu from './Menu';

const AttrNavigation = (props) => {
  const { attr, element } = props;

  // Prepare filters.
  let filters = 'stock > 0';
  if (typeof element.dataset.hierarchy !== 'undefined'
    && element.dataset.hierarchy !== '') {
    // Add filter for parent target category to show the relevant facets.
    filters = `${filters} AND field_category_name.en.lvl0: "${element.dataset.hierarchy}"`;
  }

  // Prepare ruleContexts.
  const ruleContexts = [];
  if (typeof element.dataset.ruleContext !== 'undefined'
    && element.dataset.ruleContext !== '') {
    ruleContexts.push(element.dataset.ruleContext);
  }

  return (
    <InstantSearch
      searchClient={searchClient}
      indexName={drupalSettings.shopByFilterAttribute.indexName}
    >
      <Configure
        // As we don't need any results/records from algolia, we keep this 0. We
        // only need facets to display the available options.
        hitsPerPage="0"
        filters={filters}
        ruleContexts={ruleContexts}
      />
      <Menu
        attributeAliase={attr}
        // We always want filters to use EN language data.
        attribute={`attr_${attr}.en`}
        facetOrdering
        element={element}
        limit={1000}
      />
    </InstantSearch>
  );
};

export default AttrNavigation;
