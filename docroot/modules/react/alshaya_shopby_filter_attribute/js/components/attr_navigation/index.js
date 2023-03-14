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
    // Prepare the category field for the Algolia filter based on the level. We
    // are getting this field in data attribute `data-category-field` but we
    // need to split and add the current language to match this with the Algolia
    // facet filter field name. If category field data attribute not available,
    // we will assume the category level root i.e. level 0.
    let categoryField = 'field_category_name.en.lvl0';
    if (element.dataset.categoryField !== 'undefined'
      && element.dataset.categoryField.indexOf('.') > -1) {
      // We get the field in data attribute like `field_category_name.lvl0`, but
      // our filters have language code in between `field_category_name.en.lvl0`
      // so we need to add the language code in between. For the product list
      // index, we always call default EN category filter.
      categoryField = element.dataset.categoryField.replace('.', '.en.');
    }

    // Add filters for Algolia to fetch the relevant attribute facet options
    // only. For example, if attribute filter menus are rendered under the Men's
    // L1 category, the relevant attribute facet options will render and similar
    // for the other L1 categories as well. This is so the options will match
    // from the PLP pages for the targetted category.
    filters = `${filters} AND ${categoryField}: "${element.dataset.hierarchy}"`;
  }

  // Prepare ruleContexts.
  const ruleContexts = [];
  const enableHitsPerPage = drupalSettings.algoliaSearch.hitsPerPage;

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
        filters={filters}
        ruleContexts={ruleContexts}
      />
      {enableHitsPerPage !== 0 ? (
        <Configure
          hitsPerPage="0"
        />
      ) : null}
      <Menu
        attributeAlias={attr}
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
