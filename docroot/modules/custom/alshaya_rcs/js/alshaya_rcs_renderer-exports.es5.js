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
  let html = "";
  switch (placeholder) {
    case "navigation_menu":

      const innerHtmlObj = jQuery('<div>').html(innerHtml);

      const menuListLevel1Ele = innerHtmlObj.find('.menu__list.menu--one__list');
      const l1ClickableItemEle = innerHtmlObj.find('li.level-1.clickable');
      const l1NonClickableItemEle = innerHtmlObj.find('li.level-1.non-clickable');

      if (inputs.length !== 0) {
        inputs.sort(function (a, b) {
          return parseInt(a.position) - parseInt(b.position);
        });
        let html = '';
        inputs = filterAvailableItems(inputs);
        inputs.forEach(function eachCategory(level1) {
          html += getMenuMarkup(level1, 1, innerHtmlObj, settings);
        });

        menuListLevel1Ele.find('li').remove();
        menuListLevel1Ele.append(html);
      }
      return innerHtmlObj.html();

      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for render.`);
      break;
  }

  return innerHtml;
};

const getMenuMarkup = function (levelObj, level, phHtmlObj, settings) {

  const menuPathPrefixFull = `${settings.path.pathPrefix}${settings.rcsPhSettings.categoryPathPrefix}`;
  const levelIdentifier = `level-${level}`;
  const ifChildren = (levelObj.children && levelObj.children.length > 0) ? true : false;

  var clonePhEle = null;
  if (levelObj.is_anchor) {
    clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.clickable`).clone(); //jQuery('<div>').html(l1ClickableItemEle.clone())
  }
  else {
    clonePhEle = phHtmlObj.find(`li.${levelIdentifier}.non-clickable`).clone(); //jQuery('<div>').html(l1NonClickableItemEle.clone());
  }

  // If menu has no children further, return with actual markup.
  if (!ifChildren) {
    clonePhEle.find('ul').remove();
    return clonePhEle[0]
      .outerHTML
      .replace(/#rcs.category.name#[1-9]?/g, levelObj.name)
      .replace("#rcs.category.url_path#", `/${menuPathPrefixFull}${levelObj.url_path}/`);
  }

  // If menu has children further.
  let levelHtml = '';
  clonePhEle.find('ul li, div.column').remove();

  // Build menu column for L1 dynamically, if a default layout.
  if (level === 1
    && drupalSettings.alshaya_rcs_main_menu.desktop_main_menu_layout !== 'menu_inline_display') {
    const max_nb_col = drupalSettings.alshaya_rcs_main_menu.max_nb_col;
    let ideal_max_col_length = drupalSettings.alshaya_rcs_main_menu.ideal_max_col_length;
    let reprocess = false;
    let col = 0;

    do {
      let colTotal = 0;
      let isNewColumn = false;

      col = 0;
      reprocess = false;
      levelHtml = `<div class="column ${col}">`;

      levelObj.children.forEach(function eachCategory(rcLevelObj) {
        let l2_cost = (rcLevelObj.children)
          ? rcLevelObj.children.length + 2
          : 2;

        // If we are detecting a longer column than the expected size
        // we iterate with new max.
        if (l2_cost > ideal_max_col_length) {
          ideal_max_col_length = l2_cost;
          reprocess = true;
          return;
        }

        if ((colTotal + l2_cost) > ideal_max_col_length) {
          col++;
          colTotal = 0;
          isNewColumn = true;
        }

        // If we have too many columns we try with more items per column.
        if (col >= max_nb_col) {
          ideal_max_col_length++;
          return;
        }

        if (isNewColumn) {
          levelHtml += `</div><div class="column ${col}">`;
          isNewColumn = false;
        }

        levelHtml += getMenuMarkup(rcLevelObj, (parseInt(level) + 1), phHtmlObj, settings);

        colTotal += l2_cost;
      });
    } while (reprocess || (col >= max_nb_col));

    levelHtml += '</div>';
  } else {
    // For deep level items or if not a menu inline layout.
    levelObj.children.forEach(function eachCategory(rcLevelObj) {
      levelHtml += getMenuMarkup(rcLevelObj, (parseInt(level) + 1), phHtmlObj, settings);
    });
  }

  clonePhEle.find('ul > div:first-child').append(levelHtml);

  return clonePhEle[0]
    .outerHTML
    .replace(/#rcs.category.name#[1-9]?/g, levelObj.name)
    .replace("#rcs.category.url_path#", `/${menuPathPrefixFull}${levelObj.url_path}/`);
};

const filterAvailableItems = function (catArray) {
  return catArray.filter(
    innerCatArray => (innerCatArray.include_in_menu === 1)
    );
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
