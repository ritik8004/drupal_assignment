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
      // Process rcs navigation renderer, if available.
      if (typeof globalThis.renderRcsNavigationMenu !== 'undefined') {
        html += globalThis.renderRcsNavigationMenu.render(
          settings,
          inputs,
          innerHtml,
          'navigation_menu'
        );
      }
      break;

    case "shop_by_block":
      // Process shop by block renderer, if available.
      if (typeof globalThis.renderRcsNavigationMenu !== 'undefined') {
        html += globalThis.renderRcsNavigationMenu.render(
          settings,
          inputs,
          innerHtml,
          'shop_by_block'
        );
      }
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
      if (typeof globalThis.renderRcsLhn !== 'undefined') {
        html += globalThis.renderRcsLhn.render(
          settings,
          inputs,
          innerHtml
        );
      }
      break;

    case 'super_category':
      // Render super category block.
      if (typeof globalThis.renderRcsSuperCategoryMenu !== 'undefined') {
        html += globalThis.renderRcsSuperCategoryMenu.render(
          settings,
          inputs,
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

    case "delivery-info":
    case "delivery-option":
    case 'mobile-upsell-products':
    case 'upsell-products':
    case 'mobile-related-products':
    case 'related-products':
    case 'mobile-crosssell-products':
    case 'crosssell-products':
    case 'classic-gallery':
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
      if (typeof globalThis.renderRcsPromotionalBanner !== 'undefined') {
        html += globalThis.renderRcsPromotionalBanner.render(
          settings,
          entity,
          innerHtml
        );
      }
      break;

    case 'app_navigation':
      // Render the DP App Navigation block.
      if (typeof globalThis.renderRcsAppNavigation !== 'undefined') {
        html += globalThis.renderRcsAppNavigation.render(
          settings,
          inputs,
          innerHtml
        );
      }
      break;

    case 'plp_mobile_menu':
      // Render the PLP mobile menu block.
      if (typeof globalThis.renderRcsPlpMobileMenu !== 'undefined') {
        html += globalThis.renderRcsPlpMobileMenu.render(
          settings,
          entity,
          innerHtml
        );
      }
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
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
    case 'stock_qty':
    case 'name':
    case 'description':
    case 'short_description':
    case 'promotions':
    case 'teaser_image':
      if (typeof globalThis.renderRcsProduct !== 'undefined') {
        value += globalThis.renderRcsProduct.computePhFilters(input, filter);
      }
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`);
  }

  return value;
};
