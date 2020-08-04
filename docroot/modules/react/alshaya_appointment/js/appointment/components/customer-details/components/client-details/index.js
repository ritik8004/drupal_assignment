import React from 'react';
import { getStorageInfo } from '../../../../../utilities/storage';
import TextField from '../../../../../utilities/textfield';

export default class ClientDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const {
      clientData,
    } = this.props;

    let firstName; let lastName; let dob; let email; let
      mobile = '';
    if (clientData) {
      firstName = clientData.firstName;
      lastName = clientData.lastName;
      dob = clientData.dob;
      email = clientData.email;
      mobile = clientData.mobile;
    }

    if (mobile !== undefined) {
      // Remove whitespaces when received from API.
      mobile = mobile.replace(/\s/g, '');
    }

    return (
      <div className="appointment-user-details-wrapper">
        <div className="details-header-wrapper">
          <div className="store-header appointment-subtitle">{Drupal.t('Appointment Booked by')}</div>
          <div className="user-detail-subheading">{Drupal.t('We will only use these details if we need to contact you about the appointment.')}</div>
        </div>
        <div className="user-details-wrapper">
          <div className="user-detail-name-wrapper">
            <div className="item user-firstname">
              <TextField
                type="text"
                required={false}
                name="firstName"
                defaultValue={firstName}
                className={firstName !== '' ? 'focus' : ''}
                label={Drupal.t('First name')}
                handleChange={this.handleChange}
              />
            </div>
            <div className="item user-lastname">
              <TextField
                type="text"
                required={false}
                name="lastName"
                defaultValue={lastName}
                className={lastName !== '' ? 'focus' : ''}
                label={Drupal.t('Last name')}
                handleChange={this.handleChange}
              />
            </div>
          </div>
          <div className="item user-dob">
            <label>{`${Drupal.t('Date of Birth')}*`}</label>
            <TextField
              type="date"
              required
              name="dob"
              defaultValue={dob}
              className={dob !== '' ? 'focus' : ''}
              handleChange={this.handleChange}
              section="clientData"
            />
          </div>
          <div className="item user-email">
            <TextField
              type="email"
              required={false}
              name="email"
              defaultValue={email}
              className={email !== '' ? 'focus' : ''}
              label={Drupal.t('Email address')}
              handleChange={this.handleChange}
            />
          </div>
          <div className="item user-mobile">
            <TextField
              type="mobile"
              required
              name="mobile"
              defaultValue={mobile}
              className={mobile !== '' ? 'focus' : ''}
              label={`${Drupal.t('Telephone/ Mobile')}*`}
              handleChange={this.handleChange}
            />
          </div>
        </div>
      </div>

    );
  }
}
