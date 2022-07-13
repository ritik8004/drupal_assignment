import React from 'react';
import ReactDOM from 'react-dom';
import ProductCategoryCarousel from './components/product-category-carousel';

const alshayaCategoryCarousel = jQuery('.alshaya-product-category-carousel');
// There can be a situation where we have multiple product category carousel.
alshayaCategoryCarousel.each((key, item) => {
  // Passing category id to extract the data associated with the individual
  // category id.
  const {
    pccId,
  } = jQuery(item).data();

  const carouselData = window.commerceBackend.getCarouselData(pccId);
  ReactDOM.render(
    <ProductCategoryCarousel
      categoryId={pccId}
      categoryField={carouselData.category_field}
      hierarchy={carouselData.hierarchy}
      itemsPerPage={carouselData.itemsPerPage}
      ruleContext={carouselData.ruleContext}
      sectionTitle={carouselData.sectionTitle}
      vatText={carouselData.vatText}
    />,
    item,
  );
});
