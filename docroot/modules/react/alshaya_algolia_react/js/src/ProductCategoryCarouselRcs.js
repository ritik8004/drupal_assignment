import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import ConditionalView from '../../../js/utilities/components/conditional-view';
import ProductCategoryCarousel from './components/product-category-carousel';

const ProductCategoryCarouselWrapper = ({ slug }) => {
  const [carouselData, setCarouselData] = useState({
    categoryId: null,
    categoryField: null,
    hierarchy: null,
    itemsPerPage: null,
    ruleContext: null,
    sectionTitle: null,
    vatText: null,
  });
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
  const formatCleanRuleContext = (ruleContext) => {
    let context = ruleContext.trim().toLowerCase();
    // Remove special characters.
    context = context.replace('/[^a-zA-Z0-9\\s]/', '');
    // Ensure duplicate spaces are replaced with single space.
    // H & M would have become H  M after preg_replace.
    context = context.replace('  ', ' ');
    // Replace spaces with underscore.
    context = context.replace(' ', '_');
    return context;
  };

  useEffect(() => {
    const carousel = window.commerceBackend.getCarouselData();

    global.rcsPhCommerceBackend.getData('category_parents_by_path', {
      urlPath: slug,
    }).then((response) => {
      // There might be some error or the slug entered is incorrect.
      if (!Drupal.hasValue(response)) {
        setLoaded(true);
      }
      const parents = Array.isArray(response.breadcrumbs) ? response.breadcrumbs : [];
      const categoryId = atob(response.uid);
      const hierarchyList = [];
      const contextList = [];
      const contexts = [];
      let data = {};

      const prepareData = function prepareData(categoryName) {
        hierarchyList.push(categoryName);
        const contextListItem = formatCleanRuleContext(categoryName);
        contextList.push(contextListItem);
        // Merge term name for to use multiple contexts for category pages.
        contexts.push(contextList.join('__'));
      };

      parents.forEach((parent) => {
        prepareData(parent.category_name);
      });
      // Process the current category name also.
      prepareData(response.name);

      // Merge the rule contexts with webContexts.
      const ruleContexts = contexts.reverse();
      const webContexts = ruleContexts.map((context) => `web__${context}`);

      data = {
        hierarchy: hierarchyList.join(' > '),
        level: contexts.length,
        ruleContext: ruleContexts.concat(webContexts),
        categoryField: `field_category_name.lvl${contexts.length - 1}`,
        categoryId,
        sectionTitle: carousel[slug],
      };

      data = Object.assign(data, carousel);

      setCarouselData(data);
      setLoaded(true);
    });
  }, []);

  return (
    <ConditionalView condition={loaded === true}>
      <ProductCategoryCarousel
        categoryId={carouselData.categoryId}
        categoryField={carouselData.categoryField}
        hierarchy={carouselData.hierarchy}
        itemsPerPage={carouselData.itemsPerPage}
        ruleContext={carouselData.ruleContext}
        sectionTitle={carouselData.sectionTitle}
        vatText={carouselData.vatText}
      />
    </ConditionalView>
  );
};

const alshayaCategoryCarousel = document.getElementsByClassName('alshaya-product-category-carousel');
for (let i = 0; i < alshayaCategoryCarousel.length; i++) {
  const { slug, sectionTitle } = alshayaCategoryCarousel[i].dataset;
  ReactDOM.render(
    <ProductCategoryCarouselWrapper slug={slug} sectionTitle={sectionTitle} />,
    alshayaCategoryCarousel[i],
  );
}
