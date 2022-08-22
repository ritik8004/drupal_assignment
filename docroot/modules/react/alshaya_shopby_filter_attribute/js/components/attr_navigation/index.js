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
    // Add filters for Algolia to fetch the relevant attribute facet options
    // only. For example, if attribute filter menus are rendered under the Men's
    // L1 category, the relevant attribute facet options will render and similar
    // for the other L1 categories as well. This is so the options will match
    // from the PLP pages for the targetted category.
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
        // Prepare the attribute as per the Algolia facet data.
        attribute={`attr_${attr}.${drupalSettings.path.currentLanguage}`}
        facetOrdering
        element={element}
        limit={1000}
      />
    </InstantSearch>
  );
};

export default AttrNavigation;
