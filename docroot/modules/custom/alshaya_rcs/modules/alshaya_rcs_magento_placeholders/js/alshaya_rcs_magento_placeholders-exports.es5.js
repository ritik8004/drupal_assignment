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
  const pageType = rcsPhGetPageType();

  switch (placeholder) {
    case "delivery-option":
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

    case "navigation_menu":
      // Process rcs navigation renderer, if available.
      if (typeof globalThis.renderRcsNavigationMenu !== 'undefined') {
        html += globalThis.renderRcsNavigationMenu.render(
          settings,
          inputs,
          innerHtml
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
      // Render lhn based on the page type.
      if (pageType === 'category'
      && typeof globalThis.renderRcsLhn !== 'undefined') {
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

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
      break;
  }

  return html;
};

exports.computePhFilters = function (input, filter) {
  let value = '';

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
    case 'stock_qty':
    case 'title':
    case 'description':
    case 'short_description':
      if (typeof globalThis.renderRcsProduct !== 'undefined') {
        value += globalThis.renderRcsProduct.computePhFilters(input, filter);
      }
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`);
  }

  return value;
};
