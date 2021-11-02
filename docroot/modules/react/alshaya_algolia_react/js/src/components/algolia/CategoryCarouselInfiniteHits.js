import React, { useEffect } from 'react';
import connectInfiniteHits from './connectors/connectInfiniteHits';
import ProductCategoryTeaser from '../product-category-carousel-teaser';
import ConditionalView from '../../../common/components/conditional-view';

const CategoryCarouselInfiniteHits = connectInfiniteHits(({
  hits, gtmContainer,
}) => {
  useEffect(() => {
    // Trigger the Drupal JS once the component is mounted properly.
    Drupal.attachBehaviors(document, drupalSettings);
  });

  return (
    <>
      <ConditionalView condition={hits.length > 0}>
        <div className="owl-carousel product-category-carousel">
          {
            hits.map((hit) => (
              <ProductCategoryTeaser
                key={hit.objectID}
                hit={hit}
                gtmContainer={gtmContainer}
              />
            ))
          }
        </div>
      </ConditionalView>
    </>
  );
});

export default CategoryCarouselInfiniteHits;
