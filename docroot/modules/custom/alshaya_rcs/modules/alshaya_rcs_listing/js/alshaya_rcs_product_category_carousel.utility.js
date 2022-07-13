(function ($, Drupal, drupalSettings) {
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

  /**
   * Gets the carousel accordion markup.
   *
   * @param {string} slug
   *   Slug value.
   * @param {string} title
   *   Carousel title.
   * @param {string} viewAllText
   *   View all text.
   *
   * @returns {string}
   *   Carousel accordion markup.
   */
  function getCarouselAccordionMarkup(slug, title, viewAllText) {
    return globalThis.rcsPhCommerceBackend.getData('category_children_by_path', {
      urlPath: slug,
    }).then((response) => {
      var children = Array.isArray(response.children) ? response.children : [];
      var termData = [];
      var currentLanguage = drupalSettings.path.currentLanguage;

      if (!children.length) {
        return '';
      }

      children.forEach(function (child) {
        termData.push({
          path: '/' + currentLanguage + '/' + child.url_path,
          label: child.name,
          active_class: '',
        });
      });

      return handlebarsRenderer.render('carousel.accordion', {
        title: title,
        content: termData,
        view_all_link: '/' + slug,
        view_all: Drupal.hasValue(viewAllText),
        view_all_text: viewAllText,
      });
    });
  }

  // Fetches data for and populates the carousel accordion.
  var $carouselAccordions = $('.alshaya-product-category-carousel-accordion');
  $carouselAccordions.length && $carouselAccordions.each(function (key, accordion){
    var $accordion = $(accordion);

    getCarouselAccordionMarkup(
      $accordion.data('slug'),
      $accordion.data('title'),
      $accordion.data('view-all').text,
    ).then(function (carousel) {
      $accordion.append(carousel);
    });
  });
})(jQuery, Drupal, drupalSettings);
