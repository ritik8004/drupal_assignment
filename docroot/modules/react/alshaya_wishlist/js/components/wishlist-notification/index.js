import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { isAnonymousUser, getWishlistLabel } from '../../../../js/utilities/wishlistHelper';

const WishlistNotification = ({
  notificationItemData,
}) => {
  // Early return if there is no item to show the notification.
  if (notificationItemData === null) {
    return null;
  }

  return (
    <div className="wishlist-notification notification">
      <div className="product-name">
        <span>{notificationItemData.title}</span>
        <ConditionalView condition={isAnonymousUser()}>
          <span>{Drupal.t('saved to your @wishlist_label on this visit', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}</span>
        </ConditionalView>
        <ConditionalView condition={!isAnonymousUser()}>
          <span>{Drupal.t('saved to your @wishlist_label', { '@wishlist_label': getWishlistLabel() }, { context: 'wishlist' })}</span>
        </ConditionalView>
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
            <a href={Drupal.url('user/login')} className="sign-in">{Drupal.t('Sign in', {}, { context: 'wishlist' })}</a>
            <a href={Drupal.url('user/register')} className="register">{Drupal.t('Register', {}, { context: 'wishlist' })}</a>
          </div>
        </div>
      </ConditionalView>
    </div>
  );
};

export default WishlistNotification;
