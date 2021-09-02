import * as entity from '../data/category.json';

describe('Breadcrumbs', () => {
  describe('Category page', () => {
    const breadcrumbRenderer = require('../../../../alshaya_rcs/modules/alshaya_rcs_listing/js/alshaya_rcs_listing_renderer_breadcrumb-exports.es5');

    it('With null value.', () => {
      const breadcrumbs = breadcrumbRenderer.normalize(null);
      expect(breadcrumbs).toEqual([]);
    });

    it('With empty data.', () => {
      const breadcrumbs = breadcrumbRenderer.normalize({});
      expect(breadcrumbs).toEqual([]);
    });

    it('Breadcrumb with 3 levels.', () => {
      const breadcrumbs = breadcrumbRenderer.normalize(entity);
      expect(breadcrumbs[0].url).toEqual('hand-soaps');
      expect(breadcrumbs[1].url).toEqual('hand-soaps/hand-care');
      expect(breadcrumbs[2].url).toEqual(null);

      expect(breadcrumbs[0].text).toEqual('Hand Soaps');
      expect(breadcrumbs[1].text).toEqual('hand care');
      expect(breadcrumbs[2].text).toEqual('Handy Soap');
    });

    it('Breadcrumb with 2 levels.', () => {
      entity.breadcrumbs.shift();
      const breadcrumbs = breadcrumbRenderer.normalize(entity);
      expect(breadcrumbs[0].url).toEqual('hand-soaps/hand-care');
      expect(breadcrumbs[1].url).toEqual(null);

      expect(breadcrumbs[0].text).toEqual('hand care');
      expect(breadcrumbs[1].text).toEqual('Handy Soap');
    });

    it('Breadcrumb for root level category.', () => {
      delete entity.breadcrumbs;
      const breadcrumbs = breadcrumbRenderer.normalize(entity);
      expect(breadcrumbs[0].url).toEqual(null);
      expect(breadcrumbs[0].text).toEqual('Handy Soap');
    });

  });
});
