import React from 'react';
import { setStorageInfo } from '../../../utilities/storage';

export default class AppointmentLogin extends React.Component {
  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
  }

  handleSubmit = () => {
    setStorageInfo(this.state);
    const { handleSubmit } = this.props;
    handleSubmit();
  }

  render() {
    const { socialLoginEnabled } = drupalSettings.alshaya_appointment;

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
          { socialLoginEnabled
          && (
          <div className="appointment-social-login">
            <p>{ Drupal.t('Or Sign in with') }</p>
            <div>
              <div className="alshaya-social">
                <a
                  className="social_auth_facebook auth-link"
                  href="#"
                  social-auth-link="/en/user/login/facebook"
                >
                  <span
                    className="social-network-text"
                  >
                    sign up with Facebook
                  </span>
                </a>
              </div>
              <div className="alshaya-social">
                <a
                  className="social_auth_google auth-link"
                  href="#"
                  social-auth-link="/en/user/login/google"
                >
                  <span
                    className="social-network-text"
                  >
                    sign up with Google
                  </span>
                </a>
              </div>
            </div>
          </div>
          )}
        </div>
        <div className="appointment-without-account">
          <div>
            <h2>{ Drupal.t('I don\'t have an account') }</h2>
            <p>{ Drupal.t('Log in for faster booking and to manage your appointments online') }</p>
          </div>
          <div>
            <a href="/user/register?destination=/appointment/booking">
              { Drupal.t('Register') }
            </a>
            <button
              className="appointment-store-button select-store"
              type="button"
              onClick={() => this.handleSubmit}
            >
              {Drupal.t('Continue as Guest')}
            </button>
          </div>
          { socialLoginEnabled
          && (
          <div className="appointment-social-login">
            <p>{ Drupal.t('Or Sign in with') }</p>
            <div>
              <div className="alshaya-social">
                <a
                  className="social_auth_facebook auth-link"
                  href="#"
                  social-auth-link="/en/user/login/facebook"
                >
                  <span
                    className="social-network-text"
                  >
                    sign up with Facebook
                  </span>
                </a>
              </div>
              <div className="alshaya-social">
                <a
                  className="social_auth_google auth-link"
                  href="#"
                  social-auth-link="/en/user/login/google"
                >
                  <span
                    className="social-network-text"
                  >
                    sign up with Google
                  </span>
                </a>
              </div>
            </div>
          </div>
          )}
        </div>
        <div>
          <button
            className="appointment-store-button back"
            type="button"
            onClick={() => this.handleBack('select-time-slot')}
          >
            {Drupal.t('BACK')}
          </button>
        </div>
      </div>
    );
  }
}
