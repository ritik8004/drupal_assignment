// @codingStandardsIgnoreFile
// This is because the linter is throwing errors where we use backticks here.
// Once we enable webapack for the custom modules directory, we should look into
// removing the above ignore line.
exports.render = function render(
  settings,
  placeholder,
  params,
  inputs,
  entity,
  langcode,
  innerHtml
) {
  const s = settings.rcsPhSettings;

  let html = "";
  switch (placeholder) {
    case "navigation_menu":
      const menuRegex = /(.*<ul[^>]*>.*)(<li[^>]*>.*<a[^>]*>.*<\/a>.*<ul[^>]*>.*)(<li[^>]*>.*<a[^>]*>.*<\/a>.*<ul[^>]*>.*)(<li[^>]*>.*<a[^>]*>.*<\/a>.*<\/li>)(<\/ul>.*<\/li>)(<\/ul>.*<\/li>)(<\/ul>)/;
      const menuMatches = innerHtml.match(menuRegex);

      if (menuMatches && menuMatches.length !== 0) {
        html += menuMatches[1];
        inputs.forEach(function eachCategory(level1) {
          html += menuMatches[2]
            .replace("#link", `/${s.categoryPathPrefix}${level1.slug}`)
            .replace("#title", `${level1.name}`);
          level1.children.forEach(function eachCategory(level2) {
            html += menuMatches[3]
              .replace("#link", `/${s.categoryPathPrefix}${level2.slug}`)
              .replace("#title", `${level2.name}`);
            level2.children.forEach(function eachCategory(level3) {
              html += menuMatches[4]
                .replace("#link", `/${s.categoryPathPrefix}${level3.slug}`)
                .replace("#title", `${level3.name}`);
            });
            html += menuMatches[5];
          });
          html += menuMatches[6];
        });
        html += menuMatches[7];
      }
      break;

    case "product_category_list":
      const templateRegex = /(<div id="product_teaser" class="rcs-template">(.*)<div class="rcs-end-tpl"><\/div><\/div>)/s;
      const templateMatches = innerHtml.match(templateRegex);

      if (templateMatches && templateMatches.length !== 0) {
        html += templateMatches[1];
        html += '<div class="coh-style-shop-filter">';

        html += '<div class="shop-results-wrapper"><span>' + rcsTranslatedText('Showing @from-@to of @total results', {'@from': inputs.offset+1, '@to': inputs.offset+inputs.count, '@total': inputs.total}) + '</span></div>';
        html += '<div class="shop-toggles-wrapper">';
        html += `<div class="shop-filter"><button class="shop-filter-active" onclick="jQuery('.shop-filter-wrapper').toggleClass('coh-hidden-xl');">${ rcsTranslatedText('Filter') }</button></div>`;
        html += `<div class="shop-sort"><button class="shop-sort-active" onclick="jQuery('.shop-sort-wrapper').toggleClass('coh-hidden-xl');">${ rcsTranslatedText('Sort by') }</button></div>`;
        html += '</div>';

        // Build an array from the query arguments. The key is the facet name.
        // The value is an array of selected values.
        // ?color=red&size=40&color=black
        // ['color': ['red', 'black'], 'size': ['40']]
        let qs = [];
        if (rcsWindowLocation().search.length) {
          for (const q of rcsWindowLocation().search.substring(1).split('&')) {
            let t = q.split('=');

            // Because "sort" is a native JS function, using it as an array key is
            // problematic. Use sorting as a technical key instead. Use sort as the
            // frontend query argument only.
            if (t[0] === "sort") {
              qs["sorting"] = t[1];
            }
            else {
              if (qs[String(t[0])] === undefined) {
                qs[String(t[0])] = [];
              }
              qs[String(t[0])].push(decodeURIComponent(t[1]));
            }
          }
        }

        html += '<div class="shop-filter-wrapper coh-hidden-xl">';
        const facets = JSON.parse(params.facets);
        for (const facetKey in facets) {
          if(facets.hasOwnProperty(facetKey) && inputs.facets.hasOwnProperty(`variants.attributes.${ facets[facetKey].attribute }`)) {
            const facetResults = inputs.facets[`variants.attributes.${ facets[facetKey].attribute }`];

            html += `<div class="product-filter"><h5>${ facets[facetKey].label }</h5><ul>`;
            for (const item of facetResults.terms) {
              let checked = (qs.hasOwnProperty(facetKey) && qs[facetKey].includes(item.term)) ? 'checked' : '';

              // Prepare the query string for this facet item. By default, the
              // query string contains the existing query string + the current
              // facet item. In case the current facet item is already part of
              // the query string, we remove it so it makes possible to
              // unselect a facet item.
              let q = '';
              for (const [key, values] of Object.entries(qs)) {
                for (const value of values) {
                  if (key !== "sorting" && (key !== facetKey || (key === facetKey && value !== item.term))) {
                    q += `&${ key }`;
                    if (value !== undefined) {
                      q += `=${value}`;
                    }
                  }
                }
              }
              if (!checked.length) {
                q += `&${ facetKey }=${ item.term }`;
              }
              if (qs["sorting"] !== undefined) {
                q += `&sort=${ qs["sorting"] }`;
              }
              if (q.length) {
                q = '?' + q.substring(1);
              }

              html += `<li><label><a href="${ rcsWindowLocation().pathname + q }"><input type="checkbox" name="${ facetKey }" value="${ item.term }" ${ checked }/> ${ item.term } (${ item.productCount })</a></label></li>`;
            }
            html += '</ul></div>';
          }
        }
        html += '</div>';

        html += '<div class="shop-sort-wrapper coh-hidden-xl"><ul>';
        const sorts = JSON.parse(params.sorts);
        let uriWithoutSort = rcsWindowLocation().search;
        if (qs["sorting"] !== undefined) {
          uriWithoutSort = uriWithoutSort.replace(`&sort=${ qs["sorting"] }`, '').replace(`sort=${ qs["sorting"] }&`, '');
        }
        for (const sortKey in sorts) {
          let active = false;
          if ((qs["sorting"] !== undefined && sortKey == qs["sorting"]) || (qs["sorting"] === undefined && sortKey === "default")) {
            active = true;
          }

          if (sortKey !== "default") {
            uriWithoutSort += uriWithoutSort.length ? '&sort=' + sortKey : '?sort=' + sortKey;
          }

          html += '<li>';
          html += active ? '<b>' : `<a href="${  + uriWithoutSort}">`;
          html += `${sorts[sortKey].label }`
          html += active ? '</b>' : '</a>';
          html += '</li>';
        }
        html += '</ul></div>';

        html += '</div>'
        html += '<div class="products-list-wrapper">';

        const template = templateMatches[2];
        let index = 0;
        inputs.results.forEach(function eachProduct(prod) {
          let workingTemplate = template;

          rcsPhReplaceEntityPh(template, "productItem", prod, langcode)
            .forEach(function eachReplacement(replacement) {
              workingTemplate = rcsReplaceAll(workingTemplate, replacement[0], replacement[1]);
            });

          if (index === 0 || index%4 === 0) {
            if (index !== 0) {
              html += '</div></div>';
            }

            html += '<div class="coh-row coh-row-xl coh-row-visible-xl"><div class="coh-row-inner">'
          }

          html += '<div class="coh-column coh-visible-xl coh-col-xl-3">';
          html += workingTemplate;
          html += '</div>';

          index++;
        });
        html += '</div>';

        // @TODO: This matches only the basic cases like shop-new/2 and shop-new/2?color=red.
        // But it also matches shop-new/2-dummy as the page 2.
        const pageRegex = new RegExp('\\/(' + settings.rcsPhSettings.categoryPathPrefix + '[^\\/?]*)\\/?([0-9]*)(.*)');
        const matches =  rcsWindowLocation().pathname.match(pageRegex);
        const page = matches[2] !== "" ? parseInt(matches[2]) : 1;

        // We show the "previous" link if we are not on the first page.
        if (page > 1) {
          let link = '/' + matches[1] + '/' + (page-1) + '' + matches[3];
          html += '<a href="' + link +'">' + rcsTranslatedText('<< Previous') + '</a>';
        }

        // We show the "next" link if the number of items in previous pages
        // + the number of items in the current page is lower than the total
        // number of items.
        if ((inputs.offset + inputs.count) < inputs.total) {
          let link = '/' + matches[1] + '/' + (page+1) + '' + matches[3];
          html += '<a href="' + link +'">' + rcsTranslatedText('Next >>') + '</a>';
        }
      }
      break;

    case 'product_related_list':
      if (inputs === null) {
        return '';
      }

      const templateTeaserRegex = /(<div id="product_teaser" class="rcs-template">(.*)<div class="rcs-end-tpl"><\/div><\/div>)/s;
      const templateTeaserMatches = innerHtml.match(templateTeaserRegex);

      if (templateTeaserMatches && templateTeaserMatches.length !== 0) {
        html += templateTeaserMatches[1];

        html += '<h2>' + rcsTranslatedText('Complete the look') + '</h2>';
        html += '<div class="coh-container">';

        const template = templateTeaserMatches[2];
        let index = 0;
        inputs.forEach(function eachProduct(prod) {
          let workingTemplate = template;

          rcsPhReplaceEntityPh(template, "productItem", prod, langcode)
            .forEach(function eachReplacement(replacement) {

              // We can't do a simple replace() as it would only replace the
              // first occurrence in the template. We are using a regex so it
              // it replaces all the occurrences. The placeholder replacement
              // must be escaped so all the regex specific characters we use in
              // the placeholders are not conflicting with the regex.
              workingTemplate = rcsReplaceAll(workingTemplate, replacement[0], replacement[1]);
            });

          if (index === 0 || index%4 === 0) {
            if (index !== 0) {
              html += '</div></div>';
            }

            html += '<div class="coh-row coh-row-xl coh-row-visible-xl"><div class="coh-row-inner">'
          }

          html += '<div class="coh-column coh-visible-xl coh-col-xl-3">';
          html += workingTemplate;
          html += '</div>';

          index++;
        });

        html += '</div>';
      }
      break;

    case 'entity':
      if (params['type'] == 'product') {
        let product = innerHtml;

        rcsPhReplaceEntityPh(innerHtml, "productItem", inputs, langcode)
          .forEach(function eachReplacement(replacement) {
            product = rcsReplaceAll(product, replacement[0], replacement[1]);
          });

        html += product;
      }
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
      break;
  }

  return html;
};

exports.computePhFilters = function (input, filter) {
  let value = input;

  switch(filter) {
    case 'price':
      const priceVal = globalThis.rcsCommerceBackend.getFormattedAmount(input.price.regularPrice.amount.value);
      const finalPriceVal = globalThis.rcsCommerceBackend.getFormattedAmount(input.price.maximalPrice.amount.value);
      const discountVal = globalThis.rcsCommerceBackend.calculateDiscount(priceVal, finalPriceVal);

      const price = jQuery('.rcs-templates--price').clone();
      jQuery('.price-amount', price).html(priceVal);

      const priceBlock = jQuery('.rcs-templates--price_block').clone();

      if (finalPriceVal !== priceVal) {
        const finalPrice = jQuery('.rcs-templates--price').clone();
        jQuery('.price-amount', finalPrice).html(finalPriceVal);

        jQuery('.has--special--price', priceBlock).html(price.html());
        jQuery('.special--price', priceBlock).html(finalPrice.html());

        let discount = jQuery('.price--discount').html();
        discount = discount.replace('@discount', discountVal);
        jQuery('.price--discount', priceBlock).html(discount);
      }
      else {
        // Replace the entire price block html with this one.
        priceBlock.html(price);
      }

      value = jQuery(priceBlock).html();
      break;

    case 'quantity':
      // @todo Check for how to fetch the max sale quantity.
      const quantity = parseInt(drupalSettings.alshaya_spc.cart_config.max_cart_qty, 10);
      const quantityDroprown = jQuery('.edit-quantity');
      // Remove the quantity filter.
      quantityDroprown.html('');

      for (let i = 1; i <= quantity; i++) {
        if (i === 1) {
          quantityDroprown.append('<option value="' + i + '" selected="selected">' + i + '</option>');
          continue;
        }
        quantityDroprown.append('<option value="' + i + '">' + i + '</option>');
      }
      value = quantityDroprown.html();
      break;

    case 'data-sku':
      value = input.sku;
      break;

    case 'data-sku-type':
      value = input.type_id;
      break;

    case 'vat_text':
      if (drupalSettings.vat_text === '' || drupalSettings.vat_text === null) {
        $('.vat-text').remove();
      }
      value = drupalSettings.vat_text;
      break;

    default:
      console.log(`Unknown JS filter ${filter}.`)
  }

  return value;
};

/**
 * Perform additional actions when a value has been found for the filter.
 *
 * @param {string} filter
 *   The filter string.
 * @param {string} value
 *   The filter value.
 */
exports.onFilterRepalce = function (html, filter, value) {
  switch (filter) {
    case 'currency_code':
      const currency_codes = html.matchAll('(class="price-currency[^"]*")');
      for (currency_code of currency_codes) {
        html = html.replaceAll(currency_code[0], currency_code[0].replace('hidden', ''));
      }
      break;

    default:
      break;
  }
}
