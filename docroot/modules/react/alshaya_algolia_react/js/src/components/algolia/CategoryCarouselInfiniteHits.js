import React, { useEffect } from 'react';
import connectInfiniteHits from './connectors/connectInfiniteHits';
import ProductCategoryTeaser from '../product-category-carousel-teaser';
import ConditionalView from '../../../common/components/conditional-view';

const CategoryCarouselInfiniteHits = connectInfiniteHits(({
  hits, gtmContainer, categoryId, vatText,
}) => {
  useEffect(() => {
    // Trigger the Drupal JS once the component is mounted properly.
    Drupal.attachBehaviors(
      document.querySelectorAll('[data-pcc-id="@categoryId"]', {
        '@categoryId': categoryId,
      })[0],
      drupalSettings,
    );
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
                categoryId={categoryId}
                gtmContainer={gtmContainer}
                vatText={vatText}
              />
            ))
          }
        </div>
      </ConditionalView>
    </>
  );
});

export default CategoryCarouselInfiniteHits;
