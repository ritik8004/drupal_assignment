(function ($, Drupal, drupalSettings) {

  // The threshold for how far you should reach before loading related products.
  var scrollThreshold = 200;

  /**
   * Checks if the PDP is RCS PDP or not.
   *
   * @returns {Boolean}
   *  true page is RCS PDP else false.
   */
  function isRcsPdp() {
    return typeof globalThis.rcsPhGetPageType === 'function'
      ? (globalThis.rcsPhGetPageType() === 'product') ? true : false
      : false;
  }

  /**
   * All custom js for product detail page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Js for product detail page.
   */
  Drupal.behaviors.alshayaAcmProductPdp = {
    attach: function (context, settings) {

      var node = $('.entity--type-node', context).not('[data-sku *= "#"]');
      var $context = $(context);
      if ($context && $context.hasClass('entity--type-node')){
        node = $context;
      }

      if (node.length === 0) {
        return;
      }

      var skuBaseForm = $('.sku-base-form', node);

      // Disable sharing and deliver blocks for OOS.
      $('.product-out-of-stock').once('page-load').each(function () {
        $(this).find('.sharethis-wrapper').addClass('out-of-stock');
        $(this).find('.c-accordion-delivery-options').each(function () {
          if ($(this).accordion()) {
            $(this).accordion('option', 'disabled', true);
          }
        });
      });

      $('.edit-add-to-cart').once('js-to-move-error-message').on('click', function () {
        if ($(this).closest('form').hasClass('ajax-submit-prevented')) {
          $('.form-item > label.error', $(this).closest('form')).each(function () {
            var parent = $(this).closest('.form-item');
            if (parent.find('.select2Option').length > 0) {
              $('.selected-text', $(parent)).append($(this));
            }
            else {
              $('label.form-required', $(parent)).append($(this));
            }
          });
        }

        Drupal.getRelatedProductPosition();
      });

      // Trigger matchback color change on main product color change.
      $('article[data-vmode="full"] form:first .form-item-configurable-swatch').once('product-swatch-change').on('change', function () {
        // For HFD matchback, we do not want matchback product color change on
        // main product color change.
        if (!drupalSettings.changeMatchbackProductColor) {
          return false;
        }

        var selected = $(this).val();
        var sku = $(this).parents('form').attr('data-sku');
        var productKey = Drupal.getProductKeyForProductViewMode('full');
        var variantInfo = window.commerceBackend.getProductData(sku, productKey);
        // Use swatch value to update query param from pretty path.
        if (variantInfo.swatch_param !== undefined) {
          Drupal.getSelectedProductFromSwatch(sku, selected, productKey);
        }

        var viewMode = $('.horizontal-crossell article.entity--type-node').attr('data-vmode');
        $('article[data-vmode="' + viewMode + '"] .form-item-configurable-swatch option[value="' + selected + '"]').each(function () {
          var swatchSelector = $(this).parent().siblings('.select2Option');

          if (typeof swatchSelector !== 'undefined') {
            var selectedIndex = $(this).index();
            swatchSelector.find('a[data-select-index="' + selectedIndex + '"]').trigger('click');

            // Add selected sku id in matchback product URL.
            var sku = $(this).parents('form').attr('data-sku');
            var selectedValue = $(this).text();
            var variants = drupalSettings[viewMode][sku].variants
            var selectedId = '';

            // Get the entity id of the color selected.
            $.each(variants, function (key, value) {
              $.each(value.configurableOptions, function (i, e) {
                if (e.attribute_id === 'attr_color' && e.value === selectedValue) {
                  selectedId = value.id;
                }
              });
              if (selectedId !== '') {
                var productLinkSelector = $('article[data-sku="' + sku + '"]').find('a.full-prod-link');
                var productLinkValue = productLinkSelector.attr('href').split('?')[0];
                productLinkSelector.attr('href', productLinkValue + '?selected=' + selectedId);
                return false;
              }
            });
          }
        });
      });

      $('.form-select[data-configurable-code]', skuBaseForm).once('bind-js').on('change', function () {
        var form = $(this).parents('form');
        var sku = $(form).attr('data-sku');
        var combinations = window.commerceBackend.getConfigurableCombinations(sku);
        var code = $(this).attr('data-configurable-code');
        var selected = $(this).val();
        var viewMode = $(this).parents('article.entity--type-node').attr('data-vmode');

        Drupal.refreshConfigurables(form, code, selected);

        var currentSelectedVariant = $('[name="selected_variant_sku"]', form).val();
        $('[name="selected_variant_sku"]', form).val('');

        var selectedCombination = Drupal.getSelectedCombination(form);

        if (typeof combinations['byAttribute'][selectedCombination] !== 'undefined') {
          $('[name="selected_variant_sku"]', form).val(combinations['byAttribute'][selectedCombination]);
        }

        if (currentSelectedVariant != $('[name="selected_variant_sku"]', form).val()) {
          form.trigger(
            'variant-selected',
            [
              combinations['byAttribute'][selectedCombination],
              code
            ]
          );
          // Dispatching event on variant change to listen in react.
          if (drupalSettings.aura !== undefined
            && drupalSettings.aura.enabled
            && viewMode !== 'matchback'
            && viewMode !== 'matchback_mobile'
            && Drupal.hasValue(Drupal.dispatchAuraProductUpdateEvent)) {
            Drupal.dispatchAuraProductUpdateEvent($(this));
          }
        }
      });

      skuBaseForm.once('load').each(function () {
        // Use this event to add elements to the page before the variant
        // selected event is triggered.
        var skuBaseFormPreloadevent = new CustomEvent('onSkuBaseFormPreLoad');
        document.dispatchEvent(skuBaseFormPreloadevent);

        var sku = $(this).attr('data-sku');
        var viewMode = $(this).parents('article.entity--type-node').attr('data-vmode');
        var productKey = Drupal.getProductKeyForProductViewMode(viewMode);

        // Fill the view mode form field.
        $(this).parents('article.entity--type-node[data-vmode="' + viewMode + '"]').find('.product-view-mode').val(viewMode);

        var productData = window.commerceBackend.getProductData(sku, productKey);
        if (!productData) {
          return;
        }

        // On form load set order qty limit message.
        Drupal.disableLimitExceededProducts(sku, sku);

        var node = $(this).parents('article.entity--type-node:first');
        // This is used for simple products and for sofa-sectional products
        // where the variant is not selected on page load.
        window.commerceBackend.updateGallery(node, productData.layout, productData.gallery, productData.sku);

        // Dispatch event on modal load each time to perform action on load.
        // We need to load wishlist component first before we set product data.
        if (viewMode === 'modal' || viewMode === 'matchback') {
          var eventName = (viewMode === 'modal') ? 'onModalLoad' : 'onMatchbackLoad';
          var currentMatchBackLoad = new CustomEvent(eventName, {bubbles: true, detail: { data: sku }});
          document.dispatchEvent(currentMatchBackLoad);
        }

        $(this).on('variant-selected', function (event, variant, code) {
          var sku = $(this).attr('data-sku');
          var selected = $('[name="selected_variant_sku"]', $(this)).val();
          var variantInfo = productData.variants[variant];
          var parentSku = Drupal.hasValue(drupalSettings.catalogRestructuringStatus) ? variantInfo.parent_sku : sku;
          var title = variantInfo.cart_title;

          // Trigger an event on variant select.
          // Only considers variant when url is changed.
          var currentSelectedVariantEvent = new CustomEvent('onSkuVariantSelect', {
            bubbles: true,
            detail: {
              data: {
                viewMode,
                sku: parentSku,
                variantSelected: selected,
                title,
                eligibleForReturn: variantInfo.eligibleForReturn,
                price: variantInfo.finalPrice,
              }
            }
          });
          document.dispatchEvent(currentSelectedVariantEvent);

          if (typeof variantInfo === 'undefined') {
            Drupal.alshayaLogger('warning', 'Error occurred during attribute selection, sku: @sku, selected: @selected, variant: @variant', {
              '@sku': sku,
              '@selected': selected,
              '@variant': variant,
            });
            return;
          }

          $('.price-block-' + productData.identifier, node).html(variantInfo.price);

          // If its external beauty product change title on variant change.
          if (Drupal.hasValue(drupalSettings.isExternal)) {
            $('.content__title_wrapper h1').html(variantInfo.title);
          }

          if (selected === '' && drupalSettings.showImagesFromChildrenAfterAllOptionsSelected) {
            window.commerceBackend.updateGallery(node, productData.layout, productData.gallery, sku, variantInfo.sku);
          }
          else if (viewMode === 'matchback_mobile' && $(window).width() < 768) {
            Drupal.updateMatchbackMobileImage(node, variantInfo['matchback_teaser_image']);
          }
          else {
            window.commerceBackend.updateGallery(node, productData.layout, variantInfo.gallery, sku, variantInfo.sku);
          }
          // Enable add to cart button if show pre selected variant is
          // set to false.
          if (!Drupal.hasValue(drupalSettings.showPreSelectedVariantOnPdp)) {
            $(this).find('.add-to-cart-button').prop('disabled', false);
          }
          // On variant change, disable/enable Add to bag, quantity dropdown
          // and show message based on value in drupalSettings.
          Drupal.disableLimitExceededProducts(sku, selected);

          // Update quantity dropdown based on stock available for the variant.
          $('select[name="quantity"] option', this).each(function () {
            if ((parseInt($(this).val()) > parseInt(variantInfo.stock.qty))
              || (parseInt(variantInfo.stock.maxSaleQty) !== 0
              && (parseInt($(this).val()) > parseInt(variantInfo.stock.maxSaleQty)))) {
              if ($(this).is(':selected')) {
                $('select[name="quantity"] option:first').attr('selected', 'selected').prop('selected', true);
              }

              $(this).attr('disabled', 'disabled').prop('disabled', true);
            }
            else {
              $(this).removeAttr('disabled').removeProp('disabled');
            }
          });

          $('select[name="quantity"]', this).trigger('refresh');

          if (typeof variantInfo['description'] !== 'undefined') {
            for (var i in variantInfo['description']) {
              $(variantInfo['description'][i]['selector'], node).html(variantInfo['description'][i]['markup']);
            }
          }
        });

        var preSelectVariant = true;
        // Pre select a variant only if it is allowed to show pre
        // selected variant or there is exactly one variant available.
        // Enable only for full mode.
        if (!Drupal.hasValue(drupalSettings.showPreSelectedVariantOnPdp)
          && Drupal.hasValue(productData.variants)
          && Object.keys(productData.variants).length > 1
          && viewMode == 'full') {
          preSelectVariant = false;
        }
        // Proceed to get variant and trigger variant change event only if
        // it is allowed to show pre selected variant on PDP page load.
        if (preSelectVariant && Drupal.hasValue(productData.variants)) {
          var variants = productData.variants;
          // Use first sku comes with stock in settings if available.
          var firstInStockVariant = Object.values(variants).find((variant) => {
            return parseInt(variant.stock.qty, 10) > 0;
          });
          // Use first child provided in settings if available.
          // Use the first variant otherwise.
          var configurableCombinations = window.commerceBackend.getConfigurableCombinations(sku);
          var selectedSku = (typeof configurableCombinations.firstChild === 'undefined')
            ? firstInStockVariant.sku
            : configurableCombinations.firstChild;

          var selectedSkuFromQueryParam = Drupal.getSelectedProductFromQueryParam(viewMode, productData);

          if (selectedSkuFromQueryParam !== '') {
            selectedSku = selectedSkuFromQueryParam;
          }
          else if (typeof variants[selectedSku]['parent_sku'] !== 'undefined') {
            // Try to get first child with parent sku matching. This could go
            // in color split but is generic enough so added here.
            for (var i in variants) {
              if (variants[i]['parent_sku'] === sku && parseInt(variants[i].stock.qty, 10) > 0) {
                selectedSku = variants[i]['sku'];
                break;
              }
            }
          }

          var firstAttribute = $('.form-select[data-configurable-code]:first', this);
          var firstAttributeValue = configurableCombinations['bySku'][selectedSku][firstAttribute.attr('data-configurable-code')];
          $(firstAttribute).removeProp('selected').removeAttr('selected');
          $('option[value="' + firstAttributeValue + '"]', firstAttribute).prop('selected', true).attr('selected', 'selected');
          $(firstAttribute).val(firstAttributeValue).trigger('refresh').trigger('change');
        }

        // Disable add to cart button if it is disabled to show pre selected
        // variant on PDP page load.
        if (!preSelectVariant) {
          $(this).find('.add-to-cart-button').prop('disabled', true);
        }

        // Trigger an event on SKU base form load.
        var data = {
          sku: sku,
          variantSelected: $('[name="selected_variant_sku"]', $(this)).val() || $('form.sku-base-form').attr('variantselected'),
        };

        // If Online Returns feature is enabled, add eligibleForReturn to event.
        if (Drupal.hasValue(drupalSettings.onlineReturns)) {
          if (productData.type === 'simple') {
            data.eligibleForReturn = productData.eligibleForReturn;
          } else {
            // For configurable products if variant is not selected yet, we
            // do not want to display anything so by default we set the value
            // to TRUE. Example scenario: Sofas and Sectionals.
            data.eligibleForReturn = data.variantSelected
              ? productData.variants[data.variantSelected].eligibleForReturn
              : true;
          }
        }

        var skuBaseFormLoadedEvent = new CustomEvent('onSkuBaseFormLoad', { bubbles: true, detail: { data: data }});
        document.dispatchEvent(skuBaseFormLoadedEvent);
      });

      // Show images for oos product on PDP.
      $('.out-of-stock').once('load').each(function () {
        var sku = $(this).parents('article.entity--type-node:first').attr('data-sku');
        var productKey = Drupal.getProductKeyForProductViewMode($(this).parents('article.entity--type-node').attr('data-vmode'));

        var productData = window.commerceBackend.getProductData(sku, productKey);
        if (!productData) {
          return;
        }

        var node = $(this).parents('article.entity--type-node:first');
        window.commerceBackend.updateGallery(node, productData.layout, productData.gallery, sku);
      });

      // Add related products on pdp on load and scroll.
      $(window).once('updateRelatedProductsLoad').on('load scroll', function () {
        Drupal.getRelatedProductPosition();
      });

      // Add 'each' with price on change of quantity if matchback is enabled.
      if ($('.price-suffix-matchback').length) {
        $('select.edit-quantity').once('product-edit-quantity').on('change', function () {
          var quantity = $(this).val();
          var productKey = Drupal.getProductKeyForProductViewMode($(this).parents('article.entity--type-node').attr('data-vmode'));

          var eachSelector = $('.price-block-' + drupalSettings[productKey][$(this).closest('form').attr('data-sku')].identifier + ' .price-suffix-matchback');

          if (quantity > 1) {
            eachSelector.show();
          } else if (quantity <= 1) {
            eachSelector.hide();
          }
        });
      }

      if (drupalSettings.aura !== undefined && drupalSettings.aura.enabled) {
        $('select.edit-quantity').once('product-edit-quantity').on('change', function () {
          var viewMode = $(this).parents('article.entity--type-node').attr('data-vmode');

          if (viewMode !== 'matchback' && viewMode !== 'matchback_mobile') {
            // Dispatching event on quantity change to listen in react.
            Drupal.dispatchAuraProductUpdateEvent($(this));
          }
        });
      }
    }
  };

  // Subscribe to cartMiscCheck to handle the qty limit related js functionality.
  // to handle the cases like: When user has deleted local storage and page load
  // triggers restore-cart call or user log in after coming to pdp page. pdp
  // page should get updated to disable add to cart or show any qty related msg.
  document.addEventListener('cartMiscCheck',function (e) {
    if (drupalSettings.quantity_limit_enabled === false) {
      return;
    }

    if (e.detail.productData !== undefined) {
      Drupal.disableLimitExceededProducts(
        e.detail.productData.parentSku,
        e.detail.productData.variant,
        e.detail.productData.totalQty,
        e.detail.data
      );
    }
    else {
      $.each(e.detail.items, function (sku_key, cart_item) {
        Drupal.disableLimitExceededProducts(
          cart_item.parent_sku,
          cart_item.sku,
          cart_item.qty,
          e.detail.items
        );
      });
    }
  });

  Drupal.updateMatchbackMobileImage = function (product, matchback_mobile_image) {
    if (matchback_mobile_image === '' || matchback_mobile_image === null) {
      return;
    }
    else {
      $(product).find('.matchback-image-wrapper img').attr('src', matchback_mobile_image);
    }
  };

  Drupal.refreshConfigurables = function (form, selectedCode, selectedValue) {
    var sku = ($(form).parents('article.entity--type-node:first').length > 0)
      ? $(form).parents('article.entity--type-node:first').attr('data-sku')
      : $(form).attr('data-sku');

    var combinations = window.commerceBackend.getConfigurableCombinations(sku)['combinations'];

    var selectedValues = Drupal.getSelectedValues(form);
    for (var code in selectedValues) {
      if (code == selectedCode) {
        break;
      }

      if (selectedValues === null) {
        Drupal.alshayaLogger('warning', 'Error occurred during fetching nextcode from Drupal.alshayaAcmProductSelectConfiguration, sku: @sku, combinations: @combinations, selectedCode: @selectedCode, selectedValue: @selectedValue', {
          '@sku': sku,
          '@combinations': combinations,
          '@selectedCode': selectedCode,
          '@selectedValue': selectedValue,
        });
        return;
      }

      combinations = combinations[code][selectedValues[code]];
    }

    if (typeof combinations[selectedCode] === 'undefined') {
      return;
    }

    if (combinations[selectedCode][selectedValue] === 1) {
      return;
    }

    if (typeof combinations[selectedCode][selectedValue] !== 'object' || combinations[selectedCode][selectedValue] === null) {
      Drupal.alshayaLogger('warning', 'Error occurred during attribute selection, sku: @sku, combinations: @combinations, selectedCode: @selectedCode, selectedValue: @selectedValue', {
        '@sku': sku,
        '@combinations': combinations,
        '@selectedCode': selectedCode,
        '@selectedValue': selectedValue,
      });
      return;
    }

    var nextCode = Object.keys(combinations[selectedCode][selectedValue])[0];
    var nextValues = Object.keys(combinations[selectedCode][selectedValue][nextCode]);
    Drupal.alshayaAcmProductSelectConfiguration(form, nextCode, nextValues);

    var select = $('[data-configurable-code="' + nextCode + '"]', form);
    Drupal.refreshConfigurables(form, nextCode, select.val());
  };

  Drupal.alshayaAcmProductSelectConfiguration = function (form, selectedCode, possibleValues) {
    var select = $('[data-configurable-code="' + selectedCode + '"]', form);

    select.val('');

    select.find('option')
      .prop('disabled', true)
      .attr('disabled', 'disabled')
      .removeProp('selected')
      .removeAttr('selected');

    for (var i in possibleValues) {
      select.find('option[value="' + possibleValues[i] + '"]')
        .removeProp('disabled')
        .removeAttr('disabled');
    }

    // Select the attribute variant based on selected value in query param.
    var sku = $(form).attr('data-sku');
    var viewMode = $(form).parents('article.entity--type-node:first').attr('data-vmode')
    var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
    var productData = window.commerceBackend.getProductData(sku, productKey);
    var selectedSku = Drupal.getSelectedProductFromQueryParam(viewMode, productData);
    var combinations = window.commerceBackend.getConfigurableCombinations(sku);

    if (selectedSku) {
      $(select).removeProp('selected').removeAttr('selected');
      var attributeValue = combinations.bySku[selectedSku][selectedCode];
      var attributeOption = select.find('option[value="' + attributeValue + '"]');

      if (attributeOption) {
        attributeOption
          .prop('selected', true)
          .attr('selected', 'selected')
          .trigger('refresh');
        return;
      }
    }

    select.find('option:not([disabled]):first')
      .prop('selected', true)
      .attr('selected', 'selected')
      .trigger('refresh');
  };

  Drupal.getSelectedValues = function (form) {
    var selectedValues = {};
    $('[data-configurable-code]', form).each(function () {
      var selectedVal = $(this).val();
      if (selectedVal !== '' && selectedVal !== null && typeof selectedVal !== 'undefined') {
        selectedValues[$(this).attr('data-configurable-code')] = selectedVal;
      }
    }, selectedValues);

    return selectedValues;
  };

  Drupal.getSelectedCombination = function (form) {
    var selectedValues = Drupal.getSelectedValues(form);
    // Trigger an event to pass selected configurable options.
    var configurableCombinationsEvent = new CustomEvent('onConfigurationOptionsLoad', {bubbles: true, detail: { data: selectedValues }});
    document.dispatchEvent(configurableCombinationsEvent);
    var selectedCombination = '';

    for (var code in selectedValues) {
      selectedCombination += code + '|' + selectedValues[code] + '||';
    }

    return selectedCombination;
  };

  Drupal.getRelatedProductPosition = function () {
    var sku = $('article[data-vmode="full"]').attr('data-sku');
    var device = (window.innerWidth < 768) ? 'mobile' : 'desktop';
    var selector = (device == 'mobile') ? '.mobile-only-block' : '.above-mobile-block';
    var matchback = $('.horizontal-crossell' + selector);
    var upsell = $('.horizontal-upell' + selector);
    var related = $('.horizontal-related' + selector);
    var scrollPoint = window.innerHeight + window.pageYOffset;

    if ((drupalSettings.show_crosssell_as_matchback && !matchback.hasClass('matchback-processed') && device === 'mobile')
      || ((matchback.length > 0) && !matchback.hasClass('matchback-processed') && (scrollPoint > matchback.offset().top - scrollThreshold))) {
      matchback.addClass('matchback-processed');
      // Base64 encode sku so the sku with slash doesn't break the endpoint.
      window.commerceBackend.updateRelatedProducts('crosssell', sku, device);
    }
    if ((upsell.length > 0) && !upsell.hasClass('upsell-processed') && (scrollPoint > upsell.offset().top - scrollThreshold) && drupalSettings.display_upsell) {
      upsell.addClass('upsell-processed');
      // Base64 encode sku so the sku with slash doesn't break the endpoint.
      window.commerceBackend.updateRelatedProducts('upsell', sku, device);
    }
    if ((related.length > 0) && !related.hasClass('related-processed') && (scrollPoint > related.offset().top - scrollThreshold) && drupalSettings.display_related) {
      related.addClass('related-processed');
      // Base64 encode sku so the sku with slash doesn't break the endpoint.
      window.commerceBackend.updateRelatedProducts('related', sku, device);
    }
  };

  // Disable Add to bag and quantity dropdown when order limit exceed.
  Drupal.disableLimitExceededProducts = function (sku, selected, cartProductQtyArg, cartItemsArg) {
    if ($('.order-quantity-limit-message').length > 0) {
      var selectedInput = $('input[value="' + selected + '"]');
      var orderLimitMsgSelector = selectedInput.closest('.field--name-field-skus.field__items').siblings('.order-quantity-limit-message');
      var orderLimitMobileMsgSelector = selectedInput.closest('.field--name-field-skus.field__items').parents('.acq-content-product').find('.order-quantity-limit-message.mobile-only');
      var viewMode = selectedInput.parents('article.entity--type-node').attr('data-vmode');
      var productKey = Drupal.getProductKeyForProductViewMode(viewMode);
      var productData = window.commerceBackend.getProductData(sku, productKey);

      var parentInfo = productData !== null ? productData : '';
      // At parent level, sku and selected will be same.
      var variantInfo = (productData !== null
        && typeof productData['variants'] !== "undefined"
        && sku !== selected)
        ? productData['variants'][selected] : '';

      var variantToDisableSelector = selectedInput.closest('.sku-base-form');
      var orderLimitExceeded = false;
      var orderLimitExceededMsg = '<span class="order-qty-limit-msg-inner-wrapper limit-reached">' +
        Drupal.t('Purchase limit has been reached') +
        '</span>';

      var cart_items = {};
      if (cartItemsArg !== undefined) {
        cart_items = cartItemsArg
      }
      else {
        var cart = Drupal.getItemFromLocalStorage('cart_data');
        if (cart !== null && cart.cart !== null) {
          cart_items = cart.cart.items
        }
      }

      // If limit exists at parent level.
      if ((parentInfo !== '') && (typeof parentInfo.maxSaleQty !== "undefined")) {
        var variantToDisableSelector = $('input[value="' + sku + '"]').closest('.sku-base-form');
        var allVariants = parentInfo.variants ? Object.keys(parentInfo.variants) : [];

        var orderLimitMsg = typeof variantInfo.orderLimitMsg !== "undefined"
          ? variantInfo.orderLimitMsg : '';
        // If cart is not empty.
        if (typeof cart_items !== "undefined") {
          var itemQtyInCart = 0;

          if (cartProductQtyArg !== undefined) {
            itemQtyInCart = cartProductQtyArg;
          }
          else if (allVariants.length !== 0) {
            $.each( cart_items, function ( item, value ) {
              if ($.inArray( item, allVariants ) >= 0) {
                itemQtyInCart += value.qty;
              }
            });
          }
          else {
            itemQtyInCart = ($.inArray(selected, Object.keys(cart_items)) >= 0) ?
            cart_items[selected]['qty'] : 0;
          }

          if (itemQtyInCart >= parseInt(parentInfo.maxSaleQty)) {
            var orderLimitExceeded = true;
            var orderLimitMsg = orderLimitExceededMsg;
          }
        }
      }
      else if (typeof variantInfo !== 'undefined' && variantInfo !== '') {
        var orderLimitMsg = typeof variantInfo.orderLimitMsg !== "undefined"
          ? variantInfo.orderLimitMsg : '';

        // If cart is not empty.
        if (typeof cart_items !== "undefined") {
          var selectedItemInCart = $.inArray(selected, Object.keys(cart_items));
          // If selected item is in cart.
          if (selectedItemInCart >= 0) {
            var itemQtyInCart = cart_items[selected]['qty'];

            if (itemQtyInCart >= parseInt(variantInfo.stock.maxSaleQty)) {
              var orderLimitExceeded = true;
              var orderLimitMsg = orderLimitExceededMsg;
            }
          }
        }
      }

      // Disable/Enable Add to Bag and quantity dropdown.
      variantToDisableSelector.find('.edit-add-to-cart.button').prop('disabled', orderLimitExceeded);
      variantToDisableSelector.find('.edit-quantity').prop('disabled', orderLimitExceeded);

      // Add order quantity limit message.
      orderLimitMsgSelector.html(orderLimitMsg);
      orderLimitMobileMsgSelector.html(orderLimitMsg);
    }
  };

  // Cart limit exceeded for a variant.
  $.fn.LimitExceededInCart = function (sku, selected) {
    Drupal.disableLimitExceededProducts(sku, selected);
  }

  // This event is triggered on page load itself in attach (Drupal.behaviors.configurableAttributeBoxes)
  // once size boxes are shown properly
  // but since we can't control which attach is triggered first,
  // at times this event was getting bound after it was triggered. So keeping this outside attach.
  $(document).on('select-to-option-conversion-completed', '.sku-base-form.visually-hidden', function () {
    // Show add to cart form now.
    $(this).removeClass('visually-hidden');
    $(this).trigger('form-visible');

    if ($('.magazine-layout').length > 0 || $(window).width() < 768) {
      $('.content__title_wrapper').addClass('show-sticky-wrapper');
    }
  });

  Drupal.getSelectedProductFromQueryParam = function (viewMode, productInfo) {
    var selectedSku = '';
    // Use swatch from query parameter if pdp pretty path module is enabled.
    if (productInfo.swatch_param !== undefined) {
      selectedSku = Drupal.getSelectedSkuFromPdpPrettyPath(productInfo);
    }
    // Use selected from query parameter only for main product.
    var variants = productInfo['variants'];
    var selected = (viewMode === 'full')
      ? parseInt(Drupal.getQueryVariable('selected'))
      : 0;

    if (selected > 0) {
      for (var i in variants) {
        if (variants[i]['id'] === selected) {
          selectedSku = variants[i]['sku'];
          break;
        }
      }
    }

    return selectedSku;
  };

})(jQuery, Drupal, drupalSettings);
