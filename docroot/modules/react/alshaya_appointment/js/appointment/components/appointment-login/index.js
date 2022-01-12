import React from 'react';
import getStringMessage from '../../../../../js/utilities/strings';

export default class AppointmentLogin extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
  }

  handleSubmit = () => {
    Drupal.addItemInLocalStorage(
      'appointment_data',
      this.state,
      drupalSettings.alshaya_appointment.local_storage_expire * 60,
    );
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
              <h2>{ getStringMessage('sign_in_header') }</h2>
              <p>{ getStringMessage('sign_in_subheader') }</p>
            </div>
            <div className="appointment-login-buttons-wrapper">
              <a
                href={`${baseUrl}${pathPrefix}user/login?destination=/appointment/booking?step=set`}
                className="appointment-type-button appointment-signin-button"
              >
                { getStringMessage('sign_in_button') }
              </a>
            </div>
            { socialLoginEnabled
            && (
              <div className="appointment-social-login">
                <p>{ `${getStringMessage('or')} ${getStringMessage('social_sign_in_header')}` }</p>
                <div>
                  <div className="appointment-social">
                    <span
                      className="social_auth_google social-auth-link auth-link"
                      social-auth-link={`${baseUrl}${pathPrefix}user/login/google`}
                    >
                      <span
                        className="social-network-text"
                      >
                        {getStringMessage('sign_up_google')}
                      </span>
                    </span>
                  </div>
                  <div className="appointment-social">
                    <span
                      className="social_auth_facebook social-auth-link auth-link"
                      social-auth-link={`${baseUrl}${pathPrefix}user/login/facebook`}
                    >
                      <span
                        className="social-network-text"
                      >
                        {getStringMessage('sign_up_facebook')}
                      </span>
                    </span>
                  </div>
                </div>
              </div>
            )}
          </div>
          <div className="appointment-without-account">
            <div>
              <h2>{ getStringMessage('sign_up_header') }</h2>
              <p>{ getStringMessage('sign_in_subheader') }</p>
            </div>
            <div className="appointment-login-buttons-wrapper">
              <a
                href={`${baseUrl}${pathPrefix}user/register?destination=/appointment/booking?step=set`}
                className="appointment-type-button appointment-register-button"
              >
                { getStringMessage('register') }
              </a>
              <button className="appointment-type-button appointment-checkout-button select-store" type="button" onClick={this.handleSubmit}>
                {getStringMessage('continue_as_guest')}
              </button>
            </div>
            { socialLoginEnabled
            && (
            <div className="appointment-social-login">
              <p>{ `${getStringMessage('or')} ${getStringMessage('social_sign_in_header')}` }</p>
              <div>
                <div className="appointment-social">
                  <span
                    className="social_auth_google social-auth-link auth-link"
                    social-auth-link={`${baseUrl}${pathPrefix}user/login/google`}
                  >
                    <span
                      className="social-network-text"
                    >
                      {getStringMessage('sign_up_google')}
                    </span>
                  </span>
                </div>
                <div className="appointment-social">
                  <span
                    className="social_auth_facebook social-auth-link auth-link"
                    social-auth-link={`${baseUrl}${pathPrefix}user/login/facebook`}
                  >
                    <span
                      className="social-network-text"
                    >
                      {getStringMessage('sign_up_facebook')}
                    </span>
                  </span>
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
            {getStringMessage('back')}
          </button>
        </div>
      </div>
    );
  }
}
