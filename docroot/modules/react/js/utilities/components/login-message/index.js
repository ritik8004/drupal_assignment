import React from 'react';

const LoginMessage = ({
  destination,
}) => {
  const message = Drupal.t('Items are saved for this visit only. To save them for later, sign in to your account or register a new one.');

  let loginUrl = 'user/login';
  // Add the destination with the login URL, if provided.
  if (destination) {
    loginUrl += `?destination=${destination}`;
  }

  return (
    <div className="login-message">
      <div className="text">{message}</div>
      <div className="actions">
        {/* @todo: we need to try and remove wishlist contexts. */}
        <a href={Drupal.url(loginUrl)}>{Drupal.t('Sign in', {}, { context: 'wishlist' })}</a>
        <a href={Drupal.url('user/register')}>{Drupal.t('Register', {}, { context: 'wishlist' })}</a>
      </div>
    </div>
  );
};

export default LoginMessage;
