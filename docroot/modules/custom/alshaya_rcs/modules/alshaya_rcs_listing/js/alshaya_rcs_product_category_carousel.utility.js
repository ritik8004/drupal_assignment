window.commerceBackend = window.commerceBackend || {};

/**
 * Fetches the data required for building the carousel.
 *
 * @returns {Object}
 *   The data required for builing the carousel.
 */
window.commerceBackend.getCarouselData = function () {
  return drupalSettings.alshayaProductCarousel;
}

window.commerceBackend.getCarouselChildren = function () {
  globalThis.rcsPhCommerceBackend.getData('category_children', {
    urlPath: 'shop-men',
    // urlPath: slug,
  }).then((response) => {
    globalThis.rcsPhRenderingEngine.render(
      drupalSettings,
      'product-labels',
      {
        sku,
        mainSku,
        type: 'pdp',
        labelsData,
        product,
      },
    );
    // Replace placeholders of modal content with product entity.
    let finalMarkup = content.html();
    rcsPhReplaceEntityPh(finalMarkup, 'product_modal', entity, settings.path.currentLanguage)
      .forEach(function eachReplacement(r) {
        const fieldPh = r[0];
        const entityFieldValue = r[1];
        finalMarkup = rcsReplaceAll(finalMarkup, fieldPh, entityFieldValue);
      });
    content.html(finalMarkup);


    // const children = Array.isArray(response.children) ? response.children : [];
    // const termData = [];

    // children.forEach(function (child) {
    //   termData.push({
    //     path: child.url_path,
    //     label: child.name,
    //     active_class: '',
    //   });
    // });

    document.getElementsByClassName('alshaya-product-category-carousel-accordion');

    // handlebarsRenderer.render('carousel.accordion', {
    //   title: 'abcd',
    //   content: termData,
    //   view_all_link: 'View All',
    //   view_all_text: '',
    // });
  });
}
