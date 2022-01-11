import React from 'react';
import TextField from '../../../../../utilities/textfield';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class ClientDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');

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
          <div className="store-header appointment-subtitle">{getStringMessage('appointment_booked_by_label')}</div>
          <div className="user-detail-subheading">{getStringMessage('customer_details_subheader')}</div>
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
                label={getStringMessage('first_name_label')}
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
                label={getStringMessage('last_name_label')}
                handleChange={this.handleChange}
              />
            </div>
          </div>
          <div className="item user-dob">
            <label>{`${getStringMessage('dob_label')}*`}</label>
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
              label={getStringMessage('email_address_label')}
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
              label={`${getStringMessage('mobile_label')}*`}
              handleChange={this.handleChange}
            />
          </div>
        </div>
      </div>

    );
  }
}
