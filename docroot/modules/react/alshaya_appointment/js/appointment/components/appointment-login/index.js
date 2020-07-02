import React from 'react';

export default class AppointmentLogin extends React.Component {
  constructor(props) {
    super(props);

  }

  render() {
    return (
      <div className="appointment-loginn-wrapper">
        <div className="appointment-with-account">
          <div>
            <h2>{ Drupal.t('I have an account') }</h2>
            <p>{ Drupal.t('Log in for faster booking and to manage your appointments online') }</p>
          </div>
          <div>
            <a href="/user/login?destination=/appointment/booking">
              { Drupal.t('Sign in') }
            </a>
          </div>
          <div className="appointment-social-login">
            <p>{ Drupal.t('Or Sign in with') }</p>
          </div>
        </div>
        <div className="appointment-without-account">
          <div>
            <h2>{ Drupal.t('I don\'t have an account') }</h2>
            <p>{ Drupal.t('Log in for faster booking and to manage your appointments online') }</p>
          </div>
        </div>
      </div>
    );
  }
}
