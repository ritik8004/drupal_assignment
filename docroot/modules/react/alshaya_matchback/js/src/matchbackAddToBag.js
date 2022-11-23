import { createConfigurableDrawer } from "../../../js/utilities/addToBagHelper";
import { hasValue } from "../../../js/utilities/conditionsUtility";
import MatchbackAddToBag from "./add_to_bag";


(function($, Drupal) {
  Drupal.behaviors.alshayaMatchbackBehavior = {
    attach: function attach(context, settings) {
      if (hasValue(context.classList) && !context.classList.contains('crossell-title')) {
        return;
      }
      createConfigurableDrawer(true);
      var matchbackMobileElements = $('.matchback-add-to-bag');
      matchbackMobileElements.each(function eachElement(i, obj) {
        var $parent = $($(this).parents('article[data-vmode="matchback_mobile"]')[0]);
        ReactDOM.render(
        <MatchbackAddToBag
          sku={$parent.attr('data-sku')}
          url={$parent.find('.full-prod-link').attr('href')}
        />
        ,this);
      });
    }
  }
})(jQuery, Drupal);
