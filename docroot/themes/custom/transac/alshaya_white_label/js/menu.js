/**
 * @file
 * Main Menu.
 */

/* global debounce, dataLayer */

(function ($, Drupal) {

  var css = document.createElement('style');
  css.type = 'text/css';
  document.head.appendChild(css);
  function setMenuWidth() {
    var menuWidth = $('.menu--one__list').width();
    css.innerHTML = '.menu--two__list, .menu--three__list, .menu--four__list { width: ' + menuWidth + 'px}';
  }

  $(window).resize(function () {
    setMenuWidth();
  });

  // Scan for rcs menu.
  var rcsMenuSelector = '#block-alshayarcsmainmenu #rcs-ph-navigation_menu';
  var pageHasRcsMenu = $(rcsMenuSelector).length;

  Drupal.behaviors.mainMenu = {
    attach: function (context, settings) {
      // For RCS Menu, do not proceed until it is completely loaded.
      if (pageHasRcsMenu && !$(rcsMenuSelector).hasClass('rcs-loaded')) {
        return;
      }

      $('html').once('setMenuWidth').each(function () {
        setMenuWidth();
      });

      var $listItems = $('.menu__list-item');
      $listItems.each(function () {
        var linkWrapper = $(this).find('> .menu__link-wrapper');
        var submenu;
        var subMenuLevelFour;
        var activeSubMenuLevel;
        // function to display menu levels
        Drupal.showSubMenu = function (subMenuLevel, activeSubMenuLevel) {
          if (subMenuLevel.length > 0) {
            activeSubMenuLevel.addClass('has-child');
            linkWrapper.addClass('contains-sublink');
            linkWrapper.once().after('<span class="menu__in"></span>');
          }
        };

        if ($(window).width() <= 1024) {
          activeSubMenuLevel = $(this);
          subMenuLevelFour = $(this).find('> .menu__list .menu--four__list-item');
          Drupal.showSubMenu(subMenuLevelFour, activeSubMenuLevel);
          submenu = $(this).find('> .menu__list .menu--three__list-item');
          Drupal.showSubMenu(submenu, activeSubMenuLevel);
        }
        else {
          activeSubMenuLevel = $(this);
          submenu = $(this).find('> .menu__list .menu--two__list-item');
          Drupal.showSubMenu(submenu, activeSubMenuLevel);
        }
      });

      var $menuIn = $('.has-child:not(".max-depth") .menu__link-wrapper', context);
      var deviceHeight = window.innerHeight;

      // On mobile make sub-menu scrollable only when content exceeds device height
      Drupal.isMenuScrollabe = function (activeMenu, activeMenuPartent) {
        var isContentScrollabe = (!(activeMenu.prop('scrollHeight') > deviceHeight) ? 'hidden' : 'auto');
        if (activeMenuPartent.hasClass('menu__list--active')) {
          activeMenuPartent.scrollTop(0).css('overflow-y', isContentScrollabe);
          // always keep the menu--one__list scrollable
          $('.menu--one__list').scrollTop(0).css('overflow-y', 'auto');
        }
      };

      $menuIn.once('mainMenu').on('click', function () {
        var activeSubMenu = $(this).next('.menu__in').next();
        var activeSubMenuPartent = $(this).next('.menu__in').next().parents('.menu__list');
        activeSubMenu.addClass('menu__list--active');
        Drupal.isMenuScrollabe(activeSubMenu, activeSubMenuPartent);
      });

      var $menuInFirst = $('.has-child:not(".max-depth") > .menu__link-wrapper');
      $menuInFirst.on('click', function () {
        $('.menu--one__list-item.has-child').addClass('not-active');
        $(this).parent().removeClass('not-active').addClass('active-menu');
      });

      var $menuBack = $('.back--link', context);
      $menuBack.once('mainMenu').on('click', function () {
        $(this).parents('.menu__list').first().removeClass('menu__list--active');
        var activePartentMenu = $(this).parents('.menu__list').parents('.menu__list');
        var isContentScrollabe = (!(activePartentMenu.prop('scrollHeight') > deviceHeight) ? 'hidden' : 'auto');
        activePartentMenu.scrollTop(0).css('overflow-y', isContentScrollabe);
        // always keep the menu--one__list scrollable
        $('.menu--one__list').scrollTop(0).css('overflow-y', 'auto');
      });

      var $menuBackFirst = $('.menu--two__list > .menu__links__wrapper > .back--link');
      $menuBackFirst.on('click', function () {
        $('.menu--one__list-item.has-child').removeClass('not-active active-menu');
      });

      $('.mobile--menu, .mobile--search').click(function (e) {
        e.preventDefault();
      });

      $('.hamburger--menu').click(function () {
        if ($('.search-active').length > 0) {
          $('.c-header__region .block-views-exposed-filter-blocksearch-page').toggle().toggleClass('show-search');
          $('.search-active').removeClass('search-active');
        }

        $('.main--menu').addClass('menu--active');
        $('.block-alshaya-super-category .main--menu ').removeClass('menu--active');
        $('html').addClass('html--overlay');
        $('body').addClass('mobile--overlay');
        $('body').addClass('menu--open');
      });

      $('.c-menu-primary .mobile--search').once('mainMenu').on('click', function (e) {
        e.preventDefault();
        $('.c-header__region .block-views-exposed-filter-blocksearch-page').toggle().toggleClass('show-search');
        $('.c-header__region .block-views-exposed-filter-blocksearch-page input.ui-autocomplete-input').focus();
        $(this).parent().toggleClass('search-active');
      });

      // Hide mobile search box, when clicked anywhere else.
      $(window).on('click touchstart', function (e) {
        if (!$(e.target).is('.c-menu-primary .mobile--search')) {
          // Check if element is Visible.
          if ($('.c-menu-primary #block-exposedformsearchpage').is(':visible')) {
            $('.c-menu-primary #block-exposedformsearchpage').hide();
            $('.c-menu-primary .mobile--search').parent().toggleClass('search-active');
          }
        }
      });

      // Stop event from inside container to propogate out.
      $('.c-menu-primary #block-exposedformsearchpage').on('click touchstart', function (event) {
        event.stopPropagation();
      });

      // @TODO: Refactor to reduce complexity.
      $('.mobile--close').on('click', function (e) {
        $('.main--menu').removeClass('menu--active');
        $('.c-menu-secondary').removeClass('block--display');
        $('.shop').addClass('active');
        $('.account--logged_in').removeClass('active');
        $('.account').removeClass('active');
        $('html').removeClass('html--overlay');
        $('body').removeClass('mobile--overlay');
        $('body').removeClass('menu--open');
        $('.c-my-account-nav').removeClass('block--display');
        $('.mobile--close').removeClass('block--display');
        $('.remove--toggle').removeClass('remove--toggle');
        $('.menu--one__list').find('.menu__list--active').removeClass('.menu__list--active');
      });

      $('.branding__menu .has-child .menu--one__link, .branding__menu .has-child .menu--two__list').hover(function () {
        $('body').addClass('overlay overlay-main-menu');
        $('.menu--two__list li:first', this).addClass('first--child_open');
      }, function () {
        $('body').removeClass('overlay overlay-main-menu');
        $('.menu--two__list li:first', this).removeClass('first--child_open');
      });

      // Close mobile menu when clicked outside the menu.
      var mobileMenu = $('.main--menu', context);
      $('body').once('mobile-menu-close').click(function (e) {
        if (mobileMenu.hasClass('menu--active') && e.target === $('.menu--active')[0]) {
          $('.mobile--close').trigger('click');
        }
      });

      var secondaryMainMenu = $('.secondary--main--menu__list');

      $('.logged-out .account').click(function () {
        $('.account').addClass('active');
        $('.shop').removeClass('active');
        $('.c-menu-secondary').addClass('block--display');
        $('.menu--one__list').addClass('remove--toggle');
        $('.menu--one__list').removeClass('block--display');
        $('.c-menu-secondary').removeClass('remove--toggle');
        // Removing remove--toggle class from .secondary--main--menu__list if it exists
        if (secondaryMainMenu.length) {
          secondaryMainMenu.removeClass('remove--toggle');
        }
      });

      $('.logged-out .shop').click(function () {
        $('.shop').addClass('active');
        $('.account').removeClass('active');
        $('.c-menu-secondary').removeClass('block--display');
        $('.menu--one__list').removeClass('remove--toggle');
        $('.menu--one__list').addClass('block--display');
        $('.c-menu-secondary').addClass('remove--toggle');
        $('.c-my-account-nav').addClass('remove--toggle');
        // Removing block--display class from .secondary--main--menu__list if it exists
        if (secondaryMainMenu.length) {
          secondaryMainMenu.removeClass('block--display');
        }
      });

      $('.logged-in .account--logged_in').click(function () {
        $('.account--logged_in').addClass('active');
        $('.shop').removeClass('active');
        $('.c-my-account-nav').addClass('block--display');
        $('.menu--one__list').addClass('remove--toggle');
        $('.menu--one__list').removeClass('block--display');
        $('.c-my-account-nav').removeClass('remove--toggle');
        $('.c-my-account-nav').removeClass('remove--toggle');
        // Removing remove--toggle class from .secondary--main--menu__list if it exists
        if (secondaryMainMenu.length) {
          secondaryMainMenu.removeClass('remove--toggle');
        }
      });

      $('.logged-in .shop').click(function () {
        $('.shop').addClass('active');
        $('.account--logged_in').removeClass('active');
        $('.c-menu-secondary').addClass('block--display');
        $('.menu--one__list').removeClass('remove--toggle');
        $('.menu--one__list').addClass('block--display');
        $('.c-menu-secondary').addClass('remove--toggle');
        $('.c-my-account-nav').removeClass('block--display');
        // Removing block--display class from .secondary--main--menu__list if it exists
        if (secondaryMainMenu.length) {
          secondaryMainMenu.removeClass('block--display');
        }
      });

      /**
       * Add active state to the menu.
       */
      var menuHovered = 0;
      if ($('.block-alshaya-main-menu').length) {
        var parent = $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item');

        $('.branding__menu .block-alshaya-main-menu').mouseenter(function () {
          menuHovered = 1;
          $(parent).parent().addClass('active--menu--links');
        });

        $('.block-alshaya-main-menu').mouseleave(function () {
          $(parent).parent().removeClass('active--menu--links');
          menuHovered = 0;
        });
      }

      $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item').mouseenter(function () {
        if (menuHovered === 1) {
          $('ul.menu--two__list').css('transition', 'none');
          $('.menu-backdrop').css('transition', 'none');
        }
        else {
          $('ul.menu--two__list').css('transition', 'all var(--menuTiming) ease-in 300ms');
          $('.menu-backdrop').css('transition', 'all var(--menuTiming) ease-in 300ms');
        }
      });

      $('.branding__menu .block-alshaya-main-menu li.menu--one__list-item').mouseleave(function () {
        if (menuHovered === 1) {
          $('ul.menu--two__list').css('transition', 'all var(--menuTiming) ease-in 300ms');
          $('.menu-backdrop').css('transition', 'all var(--menuTiming) ease-in 300ms');
        }
      });

      // Add class for three level navigation.
      $('.menu--one__list-item:not(:has(.menu--four__list-item))').addClass('has--three-levels');

      // Set menu level2 height on desktop and tablet.
      var windowWidth = $(window).width();
      var menuLevel2 = $('.menu--two__list');
      var menuBackdrop = $('.menu-backdrop');

      function setMenuHeight() {
        if (menuLevel2.length > 0 && windowWidth > 1024) {
          var calcMaxHeight;
          if (!$('.block-alshaya-main-menu').hasClass('megamenu-inline-layout')) {
            calcMaxHeight = $('.block-alshaya-main-menu').height() + $('.block-alshaya-main-menu').offset().top;
            var maxHeight = menuLevel2.map(function () {
              return $(this).height();
            }).toArray()
              .reduce(function (first, second) {
                return Math.max(first, second);
              });

            menuBackdrop.height(maxHeight);
            menuLevel2.each(function () {
              $(this).height(maxHeight + 20);
              if (!$('.block-alshaya-main-menu').hasClass('megamenu-inline-layout')) {
                $(this).css('max-height', 'calc(100vh - ' + calcMaxHeight + 'px)');
              }
            });
          }
        }
      }
      setMenuHeight();
      $(window).resize(debounce(function () {
        setMenuHeight();
      }, 250));

      var menuTiming = $('.main--menu').attr('data-menu-timing');
      if (menuTiming !== 'undefined') {
        $(':root').css({'--menuTiming': menuTiming + 'ms'});
      }

      // Adding Class to parent on hover of a menu-item without child.
      if ($(window).width() > 1023) {
        $('.block-alshaya-main-menu li.menu--one__list-item:not(.has-child)').hover(function () {
          $(this).parent().addClass('active--menu--without__child');
        }, function () {
          $(this).parent().removeClass('active--menu--without__child');
        });
      }

      function stickyHeader() {
        $(window, context).once().on('scroll', function () {
          var position = $('.region__banner-top').outerHeight();

          if ($(this).scrollTop() > position) {
            $('.branding__menu').addClass('navbar-fixed-top');
            $('body').addClass('header--fixed');
          }
          else {
            $('.branding__menu').removeClass('navbar-fixed-top');
            $('body').removeClass('header--fixed');
          }
        });
      }

      $(window, context).on('load', function () {
        // Apply the sticky header only on non plp,srp,promo pages.
        if ($(window).width() < 768 && $('.region__banner-top').length > 0 && $('.c-plp').length < 1) {
          stickyHeader();
        }
      });

      // Push navigation data to dataLayer.
      pushNavigationDataToDataLayer();
    }
  };

  /**
   * Bind events with menu items to prepare the data
   * for pushing the data to the dataLayer.
   */
  function pushNavigationDataToDataLayer() {
    // Push navigation events in dataLayer for super category block.
    // Top navigation for VS brand.
    $('.block-alshaya-super-category').find('.menu--one__link').once().on('click', function () {
      var menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? $(this).attr('gtm-menu-title') : $(this).data('super-category-label');
      var navigationData = {
        event: 'Top Navigation',
        eventLabel: menuLabel
      };
      pushNavigationData(navigationData, true);
    });

    // Push navigation events in dataLayer for super category block.
    // Top navigation for WE, PB, PBK brand.
    $('#block-supermenu').find('ul.menu li a').once().on('click', function () {
      // Getting GTM menu label for Top menu items.
      var menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? $(this).attr('gtm-menu-title') : $(this).text();
      var navigationData = {
        event: 'Top Navigation',
        eventLabel: menuLabel
      };
      pushNavigationData(navigationData, true);
    });

    // Push navigation events in dataLayer for 1st Level in main menu.
    $('.block-alshaya-main-menu').find('.menu--one__link').once().on('click', function () {
      // Getting GTM menu label for L1 menu items.
      var menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? $(this).attr('gtm-menu-title') : $(this).text();
      var navigationData = {
        event: 'Main Navigation',
        eventLabel: menuLabel
      };
      pushNavigationData(navigationData);
    });

    // Push navigation events in dataLayer for 2nd Level.
    $('.menu--two__list-item .menu-two__link').once().on('click', function () {
      // Create the event label with parent menu item and current target link text.
      var parentLink = $(this).closest('.menu--one__list-item').find('.menu--one__link');
      // Getting GTM menu label for L1 menu items.
      var parentLabel = (typeof parentLink.attr('gtm-menu-title') !== 'undefined' && parentLink.attr('gtm-menu-title') !== false) ? parentLink.attr('gtm-menu-title') : parentLink.text();
      // Getting GTM menu label for L2 menu items.
      var menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? $(this).attr('gtm-menu-title') : $(this).text();
      var navigationData = {
        event: 'L2 Navigation',
        eventLabel: parentLabel + ' > ' + menuLabel
      };
      pushNavigationData(navigationData);
    });

    // Push navigation events in dataLayer for 3rd Level.
    $('.menu--three__link').once().on('click', function () {
      var eventName = 'L3 Navigation';
      var menuLabel = '';
      // Create the event label with parent menu item and current target link text.
      var parentLink = $(this).closest('.menu--one__list-item').find('.menu--one__link');
      // Getting GTM menu label for L1 menu items.
      var parentLabel = (typeof parentLink.attr('gtm-menu-title') !== 'undefined' && parentLink.attr('gtm-menu-title') !== false) ? parentLink.attr('gtm-menu-title') : parentLink.text();

      var nextChildLink = $(this).closest('.menu--two__list-item').find('.menu-two__link');
      // Getting GTM menu label for L2 menu items and appending L1.
      parentLabel = (typeof nextChildLink.attr('gtm-menu-title') !== 'undefined' && nextChildLink.attr('gtm-menu-title') !== false) ? (parentLabel + ' > ' + nextChildLink.attr('gtm-menu-title')) : (parentLabel + ' > ' + nextChildLink.text());

      // If the menu item is 4th level.
      if ($(this).closest('.menu__list-item').hasClass('menu--four__list-item')) {
        eventName = 'L4 Navigation';
        nextChildLink = $(this).closest('.menu--three__list-item').find('.menu--three__link');
        // Getting GTM menu label for L3 menu items and appending L1 + L2.
        menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? (parentLabel + ' > ' + nextChildLink.attr('gtm-menu-title')) : (parentLabel + ' > ' + nextChildLink.text());

        nextChildLink = $(this).closest('.menu--four__list-item').find('.menu--three__link').first();
        // Getting GTM menu label for L4 menu items and appending L1 + L2 + L3.
        menuLabel = (typeof nextChildLink.attr('gtm-menu-title') !== 'undefined' && nextChildLink.attr('gtm-menu-title') !== false) ? (menuLabel + ' > ' + nextChildLink.attr('gtm-menu-title')) : (menuLabel + ' > ' + nextChildLink.text());
      }
      else {
        // Getting GTM menu label for L3 menu items and appending L1 + L2.
        menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? (parentLabel + ' > ' + $(this).attr('gtm-menu-title')) : (parentLabel + ' > ' + $(this).text());
      }

      var navigationData = {
        event: eventName,
        eventLabel: menuLabel
      };
      pushNavigationData(navigationData);
    });

    // Push navigation events in dataLayer for 1st Level in secondary menu.
    $('.block-alshaya-secondary-main-menu').find('.menu--one__link').once().on('click', function () {
      // Getting GTM menu label for L1 menu items in secondary menu.
      var menuLabel = (typeof $(this).attr('gtm-menu-title') !== 'undefined' && $(this).attr('gtm-menu-title') !== false) ? $(this).attr('gtm-menu-title') : $(this).text();
      var navigationData = {
        event: 'Secondary Navigation',
        eventLabel: menuLabel
      };
      pushNavigationData(navigationData);
    });

    // Push navigation events in dataLayer for 3rd Level shop by filter attribute.
    $(document).on('click', '.menu--two__list-item .shop-by-filter-attribute__list-item a' , function() {
      var eventName = 'L3 Navigation';
      var menuLabel = '';
      // Create the event label with parent menu item and current target link text.
      let parentLink = $(this).closest('.menu--one__list-item').find('.menu--one__link');
      // Getting GTM menu label for L1 menu items.
      let parentLabel = (typeof parentLink.attr('gtm-menu-title') !== 'undefined' && parentLink.attr('gtm-menu-title') !== false) ? parentLink.attr('gtm-menu-title') : parentLink.text();
      // Getting GTM menu label for L2 menu items.
      let nextChildLabel = $(this).closest('.menu--two__list-item').find('.shop-by-filter-attribute__label').text();

      // Getting GTM menu label for L3 menu items and appending L1 + L2.
      menuLabel = parentLabel + ' > ' + nextChildLabel + ' > ' + $(this).text();
      var navigationData = {
        event: eventName,
        eventLabel: menuLabel
      };
      pushNavigationData(navigationData);
    });
  }

  /**
   * Push the data to the data layer.
   *
   * @param {object} navigationData
   *  Object with the dataLayer variables.
   * @param {boolean} topNavigation
   *  Check for the top or super category navigation.
   *
   * @return {boolean}
   *  Return true/false if data push is successfull.
   */
  function pushNavigationData(navigationData, topNavigation) {
    // Early return if the dataLayer isn't defined.
    if (typeof dataLayer === 'undefined') {
      return false;
    }

    // We will push navigation data as is if it's a top/super category
    // navigation item or super category isn't active on the brand site.
    if (topNavigation || $('.block-alshaya-super-category').length <= 0) {
      dataLayer.push(navigationData);
      return true;
    }

    // If super category block exist on the page, we need to prepend
    // super category label before pushing data to dataLayer.
    var superCategoryLabel = $('.block-alshaya-super-category').find('.menu--one__link.active').attr('gtm-menu-title');
    navigationData.eventLabel = superCategoryLabel + ' > ' + navigationData.eventLabel;
    dataLayer.push(navigationData);
    return true;
  }

})(jQuery, Drupal);
