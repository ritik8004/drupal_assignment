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

    return (
      <div className="appointment-user-details-wrapper">
        <div className="details-header-wrapper">
          <h3>{Drupal.t('Appointment Booked by')}</h3>
          <h4>{Drupal.t('We will only use these details if we need to contact you about the appointment.')}</h4>
        </div>
        <div className="details-wrapper">
          <div className="item">
            <TextField
              type="text"
              required={false}
              name="firstName"
              defaultValue={firstName}
              className={firstName !== '' ? 'focus' : ''}
              label={Drupal.t('First Name')}
              handleChange={this.handleChange}
            />
          </div>
          <div className="item">
            <TextField
              type="text"
              required={false}
              name="lastName"
              defaultValue={lastName}
              className={lastName !== '' ? 'focus' : ''}
              label={Drupal.t('Last Name')}
              handleChange={this.handleChange}
            />
          </div>
          <div className="item">
            <TextField
              type="date"
              required
              name="dob"
              defaultValue={dob}
              className={dob !== '' ? 'focus' : ''}
              label={`${Drupal.t('Date of Birth')}*`}
              handleChange={this.handleChange}
            />
          </div>
          <div className="item">
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
          <div className="item">
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
