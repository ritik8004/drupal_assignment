import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import ProductCategoryCarousel from './components/product-category-carousel';


const ProductCategoryCarouselWrapper = () => {
  const [carouselData, setCarouselData] = useState(null);
  const [loaded, setLoaded] = useState(false);

  /**
   * Format / Clean the rule context string.
   *
   * @param {string} context
   *   Rule context string.
   *
   * @returns {string}
   *   The formatted string.
   */
  function formatCleanRuleContext(ruleContext) {
    let context = ruleContext.trim().toLowerCase();
    // Remove special characters.
    context = context.replace('/[^a-zA-Z0-9\\s]/', '');
    // Ensure duplicate spaces are replaced with single space.
    // H & M would have become H  M after preg_replace.
    context = context.replace('  ', ' ');
    // Replace spaces with underscore.
    context = context.replace(' ', '_');
    return context;
  }

  useEffect(() => {
    const carousel = window.commerceBackend.getCarouselData();

    global.rcsPhCommerceBackend.getData('category_parents', {
      urlPath: carousel.slug,
    })
      .then((response) => {
      // If /taxonomy/term/tid page.
      // if (!term) {
      //   return {
      //     'hierarchy': '',
      //     'level': 0,
      //     'ruleContext': [],
      //     'field': '',
      //   };
      // }
        const parents = response.breadcrumbs;
        const categoryId = atob(response.uid);
        const hierarchyList = [];
        const contextList = [];
        const contexts = [];
        let data = {};

        const modifyData = function modifyData(categoryName) {
          hierarchyList.push(categoryName);
          const contextListItem = formatCleanRuleContext(categoryName);
          contextList.push(contextListItem);
          // Merge term name for to use multiple contexts for category pages.
          contexts.push(contextList.join('__'));
        };

        parents.forEach((parent) => {
          modifyData(parent.category_name);
        });
        modifyData(response.name);

        data = {
          hierarchy: hierarchyList.join(' > '),
          level: contexts.length,
          ruleContext: contexts.reverse(),
          categoryField: `field_category_name.lvl${contexts.length - 1}`,
          categoryId,
        };

        data = Object.assign(data, carousel);

        setCarouselData(data);
        setLoaded(true);
      });
  }, []);

  return (
    <>
      { loaded
        ? (
          <ProductCategoryCarousel
            categoryId={carouselData.categoryId}
            categoryField={carouselData.categoryField}
            hierarchy={carouselData.hierarchy}
            itemsPerPage={carouselData.itemsPerPage}
            ruleContext={carouselData.ruleContext}
            sectionTitle={carouselData.sectionTitle}
            vatText={carouselData.vatText}
          />
        )
        : (<h2>Loading</h2>) }
    </>
  );
};

const alshayaCategoryCarousel = jQuery('.alshaya-product-category-carousel');
alshayaCategoryCarousel.each((key, item) => {
  ReactDOM.render(
    <ProductCategoryCarouselWrapper />,
    item,
  );
});
