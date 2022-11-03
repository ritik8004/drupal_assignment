/**
 * AY Mobile Menu.
 */

(function ($, Drupal) {
  Drupal.behaviors.ayMainMenu = {
    attach: function (context, settings) {
      var menu = $('.main--menu');
      var searchBlock = $('.block-alshaya-algolia-react-autocomplete');

      if(window.innerWidth < 1025 && menu.length && searchBlock.length) {
        var menuOpenButton = $('a.mobile--menu');
        var menuCloseButton = $('.mobile--close');
        var searchInput = searchBlock.find('input');
        var searchBackButton = searchBlock.find('.algolia-search-back-icon');
        var searchClearButton = searchBlock.find('.algolia-search-cleartext-icon');

        // Closing the search if user opens the menu while search is focused.
        $(menuOpenButton).once('menu-open').click(function() {
          if(searchBlock.hasClass('focused')) {
            searchBackButton.click();
          } else if (searchBlock.hasClass('clear-icon')) {
            // When the user access page where search is already initiated,
            // the search block has a .clear-icon class but is not focused.
            searchClearButton.click();
            searchBackButton.click();
            searchInput.blur();
          }
        });

        // Closing the menu if user searches while menu is open.
        $(searchInput).once('search-block').focus(function() {
          if(menu.hasClass('menu--active')) {
            window.scrollTo(0, 0);
            menuCloseButton.click();
          }
        });
      }
    }
  }
})(jQuery, Drupal);
