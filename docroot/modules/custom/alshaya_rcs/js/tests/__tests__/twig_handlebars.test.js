import Drupal from '../globals';

describe('RCS Twig Handlebars', () => {
  describe('Renderer', () => {
    const handlebarsRenderer = require('../../../../../custom/alshaya_rcs/js/alshaya_rcs_twig_handlebars-exports.es5');

    it('Data replacement', () => {
      window.rcsTwigTemplates = {
        block: {
          block1: '<div class="label">{{ label }}{{ desc }}</div>',
        },
      };

      const data = { label: 'Foo' };
      const html = handlebarsRenderer.render('block.block1', data);
      expect(html).toEqual('<div class="label">Foo</div>');
    });

    it('Comments should be removed from output', () => {
      window.rcsTwigTemplates = {
        block: {
          block1: '<div class="label">{# This comment will be removed #}{{ label }}</div>',
        },
      };

      const data = { label: 'Foo' };
      const html = handlebarsRenderer.render('block.block1', data);
      expect(html).toEqual('<div class="label">Foo</div>');
    });

    it('If conditions (true)', () => {
      window.rcsTwigTemplates = {
        block: {
          block1: '{% if label %}<div class="label">{{ label }}</div>{% endif %}',
        },
      };

      const data = { label: 'Foo' };
      const html = handlebarsRenderer.render('block.block1', data);
      expect(html).toEqual('<div class="label">Foo</div>');
    });

    it('If conditions (false)', () => {
      window.rcsTwigTemplates = {
        block: {
          block1: '{% if label %}<div class="label">{{ label }}</div>{% endif %}<div>Bar</div>',
        },
      };

      const data = {};
      const html = handlebarsRenderer.render('block.block1', data);
      expect(html).toEqual('<div>Bar</div>');
    });

    it('String translation.', () => {
      window.rcsTwigTemplates = {
        block: {
          block1: '<div class="label">{{t \'Hello world\' }}!</div>',
        },
      };

      window.Drupal.t = () => 'Bonjour le monde';

      const data = {};
      const html = handlebarsRenderer.render('block.block1', data);
      expect(html).toEqual('<div class="label">Bonjour le monde!</div>');
    });

  });
});
