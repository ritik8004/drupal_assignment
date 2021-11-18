import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const WishlistNotification = ({
  productData,
}) => {
  if (productData === null) {
    return null;
  }
  return (
    <div className="wishlist-notification notification">
      <div className="product-name">
        <a href={`${productData.link}`} className="product-title">
          {Drupal.t('@productName saved to your @label on this visit', { '@productName': productData.name, '@label': drupalSettings.wishlist.label })}
        </a>
      </div>
      <ConditionalView condition={drupalSettings.wishlist.userDetails.id === 0}>
        <div className="wishlist-query">
          {Drupal.t('Keep it for next time?')}
        </div>
        <div className="wishlist-user-login">
          <div className="login-message">
            {Drupal.t('Sign in to your account or register a new one.')}
          </div>
          <div className="actions">
            <a href="/user/login">{Drupal.t('Sign in')}</a>
            <a href="/user/register">{Drupal.t('Register')}</a>
          </div>
        </div>
      </ConditionalView>
    </div>
  );
};

export default WishlistNotification;
