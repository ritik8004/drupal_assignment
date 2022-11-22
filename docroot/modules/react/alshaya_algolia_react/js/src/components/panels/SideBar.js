import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

export default function SideBar(props) {
  const { children } = props;
  return (
    <aside className="c-sidebar-first">
      <div className="c-sidebar-first__region">
        <div className="region region__sidebar-first clearfix">
          { drupalSettings.algoliaSearch.enable_lhn_tree_search > 0
            && (
            <div className="c-facet__blocks">
              {(drupalSettings.algoliaSearch.search.filters.super_category !== undefined)
                && hasValue(drupalSettings.superCategory)
                && (drupalSettings.superCategory.show_brand_filter) && (
                <div className="c-facet__blocks c-facet block-facet-blockcategory-facet-search supercategory-facet c-accordion">
                  <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.search.filters.super_category.label}</h3>
                  {children[0]}
                </div>
              )}
              <div className="c-facet__blocks c-facet block-facet-blockcategory-facet-search c-accordion">
                <h3 className="c-facet__title c-accordion__title c-collapse__title">{drupalSettings.algoliaSearch.category_facet_label}</h3>
                {children[1]}
              </div>
            </div>
            )}
        </div>
      </div>
    </aside>
  );
}
