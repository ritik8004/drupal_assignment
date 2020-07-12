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
    const { baseUrl, pathPrefix } = drupalSettings.path;

    return (
      <div className="appointment-login-wrapper">
        <div className="appointment-login-container">
          <div className="appointment-with-account">
            <div>
              <h2>{ Drupal.t('I have an account') }</h2>
              <p>{ Drupal.t('Log in for faster booking and to manage your appointments online') }</p>
            </div>
            <div className="appointment-login-buttons-wrapper">
              <a
                href={`${baseUrl}${pathPrefix}user/login?destination=/appointment/booking`}
                className="appointment-type-button appointment-signin-button"
              >
                { Drupal.t('Sign in') }
              </a>
            </div>
            { socialLoginEnabled
            && (
              <div className="appointment-social-login">
                <p>{ Drupal.t('Or Sign in with') }</p>
                <div>
                  <div className="appointment-social">
                    <a
                      className="social_auth_facebook social-auth-link auth-link"
                      href={() => false}
                      social-auth-link={`${baseUrl}${pathPrefix}user/login/facebook`}
                    >
                      <span
                        className="social-network-text"
                      >
                        sign up with Facebook
                      </span>
                    </a>
                  </div>
                  <div className="appointment-social">
                    <a
                      className="social_auth_google social-auth-link auth-link"
                      href={() => false}
                      social-auth-link={`${baseUrl}${pathPrefix}user/login/google`}
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
            <div className="appointment-login-buttons-wrapper">
              <a
                href={`${baseUrl}${pathPrefix}user/register?destination=/appointment/booking`}
                className="appointment-type-button appointment-register-button"
              >
                { Drupal.t('Register') }
              </a>
              <button
                className="appointment-type-button appointment-checkout-button select-store"
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
                <div className="appointment-social">
                  <a
                    className="social_auth_facebook social-auth-link auth-link"
                    href={() => false}
                    social-auth-link={`${baseUrl}${pathPrefix}user/login/facebook`}
                  >
                    <span
                      className="social-network-text"
                    >
                      sign up with Facebook
                    </span>
                  </a>
                </div>
                <div className="appointment-social">
                  <a
                    className="social_auth_google social-auth-link auth-link"
                    href={() => false}
                    social-auth-link={`${baseUrl}${pathPrefix}user/login/google`}
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
        </div>
        <div className="appointment-store-buttons-wrapper">
          <button
            className="appointment-type-button appointment-store-button back"
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
