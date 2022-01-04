/**
 * @file
 * Contains utility function for wishlist to perform after user logout.
 */

(function ($) {
	'use strict';

	Drupal.behaviors.alshayaWishlistUserLogout = {
		attach: function () {
			// Check if the 'clear_user_wishlist' cookie exists and
			// clear the wishlist local storage. This action we perform
			// for clearing wishlist information from local storage
			// once user gets logout.
			const clearWishlistCookie = $.cookie('clear_user_wishlist');
			if (clearWishlistCookie !== undefined) {
				// Remove cookie as we need to perform the action only once.
				$.removeCookie('clear_user_wishlist', { path: '/' });

				// Clear the wishlist data from the local storage.
				localStorage.removeItem('wishlistInfo');
			}
		}
	};

})(jQuery);
