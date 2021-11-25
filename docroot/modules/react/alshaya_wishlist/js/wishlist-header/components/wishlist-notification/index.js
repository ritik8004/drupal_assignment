import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { getWishlistLabel, isAnonymousUser } from '../../../utilities/wishlist-utils';

const WishlistNotification = ({
  wishListItemData,
}) => {
  if (wishListItemData === null) {
    return null;
  }
  return (
    <div className="wishlist-notification notification">
      <div className="product-name">
        {Drupal.t('@productName saved to your @wishlist_label on this visit', { '@productName': wishListItemData.title, '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}
      </div>
      <ConditionalView condition={isAnonymousUser()}>
        <div className="wishlist-query">
          {Drupal.t('Keep it for next time?', {}, { context: 'wishlist' })}
        </div>
        <div className="wishlist-user-login">
          <div className="login-message">
            {Drupal.t('Sign in to your account or register a new one.', {}, { context: 'wishlist' })}
          </div>
          <div className="actions">
            <a href="/user/login">{Drupal.t('Sign in', {}, { context: 'wishlist' })}</a>
            <a href="/user/register">{Drupal.t('Register', {}, { context: 'wishlist' })}</a>
          </div>
        </div>
      </ConditionalView>
    </div>
  );
};

export default WishlistNotification;
