(function ($) {
    'use strict';

    Drupal.behaviors.sizeFacetCopy = {
        attach: function (context, settings) {
            var StoreBandSize = [];

            // For now we want to do it for PLP only.
            $('.region__sidebar-first [data-block-plugin-id="facet_block:plp_size"]:first').once('size-copy').each(function () {
                var $wrapper = $(this);
                $('.sfb-facets-container').html('');
                var SlideCounter = 0;

                // Get all available facets.
                $wrapper.find('.facet-item').each(function () {
                    var item = $(this).find('a');

                    var $div = $('<div />');

                    // Add value from hidden anchor to copy.
                    $div.attr('data-facet-item-value', $(item).attr('data-drupal-facet-item-value'));

                    // Add classes from hidden anchor tag to copy.
                    $div.attr('class', $(item).attr('class'));


                    var $value = $('.facet-item__value', $(item)).clone();
                    $value.find('span').remove();
                    var value = $value.html().trim();
                    var bandSize = parseInt(value);

                    // This is for shop by letters.
                    if (isNaN(bandSize)) {
                        // $div.html(value)
                        if ($('div[data-facet-item-value="' + value + '"]').length === 0) {
                            $('.sfb-letter .sfb-facets-container').append($('<div attr-band-size="' + value + '" class="shop-by-size-letter"/>'));
                            $div.append('<span class="shop-by-size-alpha">' + value + '</span>');
                            $('div[attr-band-size="' + value + '"]').append($div);
                        }

                    }
                    // This is for shop by band and cup size.
                    else {
                        if ($('div[attr-band-size="' + bandSize + '"]').length === 0) {
                            $('.sfb-band-cup .sfb-facets-container').append($('<div attr-band-size="' + bandSize + '" data-position="' + SlideCounter + '" class="shop-by-size-band"/>'));
                            SlideCounter++;
                            StoreBandSize.push(bandSize);

                        }
                        $div.append('<span class="shop-by-size-cup">' + bandSize + '</span>');
                        // Find cup size now.
                        var cupSize = value.replace(bandSize, '');
                        $div.append('<span class="shop-by-size-cup">' + cupSize + '</span>');
                        $('div[attr-band-size="' + bandSize + '"]').append($div);
                    }
                });

                $('.sfb-facets-container [data-facet-item-value]').on('click', function () {
                    var $value = $(this).attr('data-facet-item-value');
                    $('.facet-item a[data-drupal-facet-item-value="' + $value + '"]', $wrapper).closest('.facet-item').trigger('click');
                });


            });


            if ($(window).width() > 1024) {
                // duration of scroll animation
                var scrollDuration = 500;

                // paddles
                var leftPaddle = $('.paddle_prev');
                var rightPaddle = $('.paddle_next');

                // get items dimensions
                var itemsLength = $('.shop-by-size-band').length;
                var itemSize = $('.shop-by-size-band').outerWidth();

                var DifferenceOfsCupsizewrapper = [];
                var sum = 0;
                $('.sfb-band-cup').find('.shop-by-size-band').each(function () {
                    // Get the distance of different cup size wrapper from starting point.
                    sum = sum + $(this).outerWidth() + 16;
                    DifferenceOfsCupsizewrapper.push(sum);
                });

                // get some relevant size for the paddle triggering point
                var paddleMargin = 0;

                // get wrapper width
                var getMenuWrapperSize = function () {
                    return $('.sfb-facets-container').outerWidth();
                };

                var menuWrapperSize = getMenuWrapperSize();
                // the wrapper is responsive
                $(window).on('resize', function () {
                    menuWrapperSize = getMenuWrapperSize();
                });

                // size of the visible part of the menu is equal as the wrapper size
                var menuVisibleSize = menuWrapperSize;

                // get total width of all menu items
                var getMenuSize = function () {
                    return $('.sfb-band-cup').outerWidth();
                };

                var menuSize = getMenuSize();
                // get how much of menu is invisible
                var menuInvisibleSize = menuSize - menuWrapperSize;

                // get how much have we scrolled to the left
                var getMenuPosition = function () {
                    return $('.sfb-facets-container').scrollLeft();
                };

                $(leftPaddle).addClass('hidden');

                // finally, what happens when we are actually scrolling the menu
                function getMenuscrollPosition() {

                    $('.sfb-facets-container').on('scroll', function () {

                        // get how much of menu is invisible
                        menuInvisibleSize = menuSize - menuWrapperSize;
                        // get how much have we scrolled so far
                        var menuPosition = getMenuPosition();

                        var menuEndOffset = menuInvisibleSize;

                        // show & hide the paddles
                        // depending on scroll position
                        if (menuPosition <= paddleMargin) {
                            $(leftPaddle).addClass('hidden');
                            $(rightPaddle).removeClass('hidden');
                        }
                        else if (menuPosition >= (DifferenceOfsCupsizewrapper[itemsLength - 1] - (menuWrapperSize + 17))) {
                            $(leftPaddle).removeClass('hidden');
                            $(rightPaddle).addClass('hidden');
                        }

                        else {
                            $(leftPaddle).removeClass('hidden');
                            $(rightPaddle).removeClass('hidden');
                        }

                    });
                }

                getMenuscrollPosition();

                var counter = 0;

                if ($('.sfb-facets-container').outerWidth() >= DifferenceOfsCupsizewrapper[itemsLength]) {
                    $(leftPaddle).addClass('hidden');
                }

                // scroll to left
                $(rightPaddle).on('click', function () {
                    $('.sfb-facets-container').animate({scrollLeft: DifferenceOfsCupsizewrapper[counter]}, scrollDuration);
                    counter++;
                });

                // scroll to right
                $(leftPaddle).on('click', function () {
                    console.log($('div[attr-band-size="' + StoreBandSize[counter + 1] + '"]').position());
                    counter--;

                    if (counter == 0) {
                        $('.sfb-facets-container').animate({scrollLeft: 0}, scrollDuration);
                    }
                    else {
                        $('.sfb-facets-container').animate({scrollLeft: (DifferenceOfsCupsizewrapper[counter] - DifferenceOfsCupsizewrapper[counter - 1])}, scrollDuration);
                    }

                });
            }
        }
    };

}(jQuery));