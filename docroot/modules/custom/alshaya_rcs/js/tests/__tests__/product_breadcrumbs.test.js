import * as entity from '../data/product.json';

describe('Breadcrumbs', () => {
  describe('Product page', () => {
    const breadcrumbRenderer = require('../../../../alshaya_rcs/modules/alshaya_rcs_product/js/alshaya_rcs_product_renderer_breadcrumb-exports.es5');

    it('With empty data.', () => {
      const breadcrumbs = breadcrumbRenderer.normalize({ categories: [] });
      expect(breadcrumbs).toEqual([]);
    });

    it('Breadcrumb with root category.', () => {
      const breadcrumbs = breadcrumbRenderer.normalize(entity);
      expect(breadcrumbs[0].url).toEqual('body-care');
      expect(breadcrumbs[1].url).toEqual('body-care/shop-all-body-care');
      expect(breadcrumbs[2].url).toEqual('body-care/shop-all-body-care/travel-size');
      expect(breadcrumbs[3].url).toEqual(null);

      expect(breadcrumbs[0].text).toEqual('Body Care');
      expect(breadcrumbs[1].text).toEqual('Test Shop All Body Care');
      expect(breadcrumbs[2].text).toEqual('Travel Size');
      expect(breadcrumbs[3].text).toEqual('Hello Beautiful Ultra Shea Body Cream');
    });

    it('Breadcrumb from entity without root category.', () => {
      entity.categories.shift();
      const breadcrumbs = breadcrumbRenderer.normalize(entity);
      expect(breadcrumbs[0].url).toEqual('hand-soaps');
      expect(breadcrumbs[1].url).toEqual('hand-soaps/hand-care');
      expect(breadcrumbs[2].url).toEqual('hand-soaps/hand-care/test-hand-care');
      expect(breadcrumbs[3].url).toEqual(null);

      expect(breadcrumbs[0].text).toEqual('Hand Soaps');
      expect(breadcrumbs[1].text).toEqual('hand care');
      expect(breadcrumbs[2].text).toEqual('test hand care');
      expect(breadcrumbs[3].text).toEqual('Hello Beautiful Ultra Shea Body Cream');
    });

    it('Breadcrumb from category with deepest level.', () => {
      entity.categories.shift();
      const breadcrumbs = breadcrumbRenderer.normalize(entity);
      expect(breadcrumbs[0].url).toEqual('body-care');
      expect(breadcrumbs[1].url).toEqual('body-care/shop-all-body-care');
      expect(breadcrumbs[2].url).toEqual('body-care/shop-all-body-care/travel-size');
      expect(breadcrumbs[3].url).toEqual(null);

      expect(breadcrumbs[0].text).toEqual('Body Care');
      expect(breadcrumbs[1].text).toEqual('Test Shop All Body Care');
      expect(breadcrumbs[2].text).toEqual('Travel Size');
      expect(breadcrumbs[3].text).toEqual('Hello Beautiful Ultra Shea Body Cream');
    });
  });
});
