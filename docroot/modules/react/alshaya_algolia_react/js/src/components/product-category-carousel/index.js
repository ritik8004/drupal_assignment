import React from 'react';
import {
  Configure,
  InstantSearch,
} from 'react-instantsearch-dom';
import { searchClient } from '../../config/SearchClient';
import CategoryCarouselInfiniteHits from '../algolia/CategoryCarouselInfiniteHits';

/**
 * Render algolia results.
 */
const ProductCategoryCarousel = ({
  searchState,
  createURL,
  onSearchStateChange,
  categoryId,
  categoryField,
  hierarchy,
  itemsPerPage,
  ruleContext,
  sectionTitle,
  vatText,
}) => {
  const {
    filterOos,
    search,
  } = drupalSettings.algoliaSearch;

  // For enabling/disabling hitsPerPage key in algolia calls.
  const enableHitsPerPage = drupalSettings.algoliaSearch.hitsPerPage;
  let finalFilter = '';

  // Do not show out of stock products.
  if (filterOos === true) {
    finalFilter = '(stock > 0) AND ';
  }
  // Add the category hierarchy.
  if (categoryField && hierarchy) {
    finalFilter = `${finalFilter}(${categoryField}: "${hierarchy}")`;
  }

  return (
    <InstantSearch
      searchClient={searchClient}
      indexName={search.indexName}
      searchState={searchState}
      createURL={createURL}
      onSearchStateChange={onSearchStateChange}
    >
      <Configure
        userToken={Drupal.getAlgoliaUserToken()}
        clickAnalytics
        {...(enableHitsPerPage && { hitsPerPage: itemsPerPage })}
        filters={finalFilter}
        ruleContexts={ruleContext}
      />
      {/* Section title for the product category carousel. */}
      <h3 className="subtitle crossell-title"><a href={sectionTitle.url}>{sectionTitle.title}</a></h3>
      <div className="views-element-container">
        <div className="view view-product-slider view-id-product_slider view-display-id-category_product_slider">
          <CategoryCarouselInfiniteHits categoryId={categoryId} vatText={vatText} />
        </div>
      </div>
    </InstantSearch>
  );
};

export default ProductCategoryCarousel;
