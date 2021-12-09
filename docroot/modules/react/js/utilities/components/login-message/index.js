import React from 'react';

const LoginMessage = () => {
  const message = Drupal.t('Items are saved for this visit only. To save them for later, sign in to your account or register a new one.');

  return (
    <div className="login-message">
      <div className="text">{message}</div>
      <div className="actions">
        <a href="/user/login">{Drupal.t('Sign in')}</a>
        <a href="/user/register">{Drupal.t('Register')}</a>
      </div>
    </div>
  );
};

export default LoginMessage;
