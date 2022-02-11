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

  ReactDOM.render(
    <ProductCategoryCarousel categoryId={pccId} />,
    item,
  );
});
