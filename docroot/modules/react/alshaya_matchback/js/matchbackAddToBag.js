import ReactDOM from 'react-dom';
import React from 'react';
import { createConfigurableDrawer } from '../../js/utilities/addToBagHelper';
import { hasValue } from '../../js/utilities/conditionsUtility';
import MatchbackAddToBag from './add_to_bag';

(function alshayaMatchbackAddToBag($, Drupal) {
  Drupal.behaviors.alshayaMatchbackBehavior = { // eslint-disable-line no-param-reassign
    attach: function attach(context) {
      // We only want to proceed when the AJAX call to fetch matchback items
      // is done and Drupal behaviors is called.
      if (hasValue(context.classList) && !context.classList.contains('crossell-title')) {
        return;
      }
      // Add the drawer warpper markup to the dom.
      createConfigurableDrawer(true);
      // Now add the Add to Bag button for each carousel item.
      const matchbackMobileElements = $('.matchback-add-to-bag');
      matchbackMobileElements.each(function eachElement() {
        const $parent = $($(this).parents('article[data-vmode="matchback_mobile"]')[0]);
        // If stock is 0, then the attribute does not appear in the markup.
        let stock = $parent.attr('data-stock');
        stock = hasValue(stock) ? stock : '0';

        ReactDOM.render(
          <MatchbackAddToBag
            sku={$parent.attr('data-sku')}
            url={$parent.find('.full-prod-link').attr('href')}
            stockQty={stock}
            productData={{ sku_type: $parent.attr('data-sku_type') }}
            isBuyable={$parent.attr('data-is_buyable')}
            extraInfo={{ showAddToBag: true }}
            wishListButtonRef={{}}
            styleCode={null}
          />,
          this,
        );
      });
    },
  };
}(jQuery, Drupal));
