exports.render = function render(
  settings,
  placeholder,
  params,
  inputs,
  entity,
  langcode,
  innerHtml
) {
  let html = "";

  switch (placeholder) {
    case "navigation_menu":
      if (typeof globalThis.mainMenuProcessor !== 'undefined') {
        const menuData = globalThis.mainMenuProcessor.prepareData(
          drupalSettings.alshayaRcs.navigationMenu,
          // Here we need to push the top level category + children
          // as the top level categories has the information about
          // Visual mobile menu.
          inputs
        )
        html = handlebarsRenderer.render('main_menu_level1', menuData);
      }
      break;

    case "shop_by_block":
      // Process and render shop by block menu.
      const shopByMenuData = globalThis.shopByMenuProcessor.prepareData(
        inputs.children
      );
      html = handlebarsRenderer.render('shop_by_menu', shopByMenuData);

      break;

    case 'product_category_list':
      // Process rcs plp renderer, if available.
      if (typeof globalThis.renderRcsListing !== 'undefined') {
        html += globalThis.renderRcsListing.render(
          entity,
          innerHtml
        );
      }
      break;

    case 'breadcrumb':
      if (typeof globalThis.renderRcsBreadcrumb !== 'undefined')  {
        html += globalThis.renderRcsBreadcrumb.render(
          settings,
          entity,
          innerHtml
        );
      }
      break;

    case 'lhn_block':
      // Render lhn based block.
      const lhnData = globalThis.lhnProcessor.prepareData(
        settings,
        inputs.children
      );

      html = handlebarsRenderer.render('lhn_menu', lhnData);
      break;

    case 'super_category':
      // Render super category block.
      if (typeof globalThis.renderRcsSuperCategoryMenu !== 'undefined') {
        html += globalThis.renderRcsSuperCategoryMenu.render(
          settings,
          inputs.children,
          innerHtml
        );
      }
      break;

    case 'promotion_page':
      // Render rcs promotion, if available.
      if (drupalSettings.rcsPage.type === 'promotion' &&
        typeof globalThis.renderRcsPromotion !== 'undefined') {
        html += globalThis.renderRcsPromotion.render(
          entity,
          innerHtml
        );
      }
      break;

    case 'field_magazine_shop_the_story':
      let data = [];
      // Sort results in the same order as in the CMS.
      JSON.parse(params.skus).forEach((sku) => {
        const i = inputs.findIndex(i => i.sku === sku);
        if (inputs[i]) {
          data.push(inputs[i]);
        }
      });

      // Render template.
      html = handlebarsRenderer.render('product.teaser', { data: data });
      break;

    case 'order_teaser':
      // Get individual table row items to perform token replacement.
      if (typeof globalThis.renderRcsOrders != 'undefined') {
        html += globalThis.renderRcsOrders.render(
          settings,
          inputs,
          innerHtml
        );
      }
      break;

    case "delivery-options":
    case 'mobile-upsell-products':
    case 'upsell-products':
    case 'mobile-related-products':
    case 'related-products':
    case 'mobile-crosssell-products':
    case 'crosssell-products':
    case 'classic-gallery':
    case 'magazine-gallery':
    case 'product-labels':
      // Render super category block.
      if (typeof globalThis.renderRcsProduct !== 'undefined') {
        html += globalThis.renderRcsProduct.render(
          settings,
          placeholder,
          params,
          inputs,
          entity,
          langcode,
          innerHtml
        );
      }
      break;

    case 'promotional_banner':
      // Render promotional banner block.
      html = handlebarsRenderer.render('promotional_banner', entity);
      break;

    case 'app_navigation':
      // Render the DP App Navigation block.
      if (typeof globalThis.renderRcsAppNavigation !== 'undefined') {
        html += globalThis.renderRcsAppNavigation.render(
          settings,
          inputs.children,
          innerHtml
        );
      }
      break;

    case 'plp_mobile_menu':
      // Render the PLP mobile menu block.
      const plpMobileMenuData = globalThis.plpMobileMenuProcessor.prepareData(
        settings,
        entity
      );

      html += handlebarsRenderer.render('plp_mobile_menu', plpMobileMenuData);
      break;

    case 'sitemap':
      // Render the sitemap page block.
      if (typeof global.sitemapPageRenderer !== 'undefined') {
        html += global.sitemapPageRenderer.render(
          settings,
          inputs.children,
          innerHtml
        );
      }
      break;

    default:
      Drupal.alshayaLogger('debug', 'Placeholder @placeholder not supported for render.', {
        '@placeholder': placeholder
      });
      break;
  }

  return html;
};

exports.computePhFilters = function (input, filter) {
  let value = '';

  // @todo Review if we really need this switch or we can call
  // globalThis.renderRcsProduct.computePhFilters directly as there is already
  // a switch there.
  switch(filter) {
    case 'price':
    case 'sku':
    case 'sku-clean':
    case 'sku-type':
    case 'vat_text':
    case 'image':
    case 'thumbnail_count':
    case 'product_thumbnails':
    case 'product_full_screen_gallery':
    case 'product_mobile_gallery':
    case 'add_to_cart':
    case 'gtm-price':
    case 'final_price':
    case 'first_image':
    case 'schema_stock':
    case 'brand_logo':
    case 'url':
    case 'old-price':
    case 'url_encode':
    case 'stock_qty':
    case 'name':
    case 'description':
    case 'short_description':
    case 'promotions':
    case 'teaser_image':
    case 'price_block_identifier':
    case 'absolute_url':
      if (typeof globalThis.renderRcsProduct !== 'undefined') {
        value += globalThis.renderRcsProduct.computePhFilters(input, filter);
      }
      break;

    default:
      Drupal.alshayaLogger('debug', 'Unknown JS filter @filter.', {'@filter': filter});
  }

  return value;
};
