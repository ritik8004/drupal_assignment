import React from 'react';
import ConfirmationItems from './components/confirmation-items';
import { getStorageInfo } from '../../../utilities/storage';
import { addressCleanup } from '../../../utilities/helper';

export default class Confirmation extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  render() {
    const {
      appointmentCategory,
      appointmentType,
      selectedStoreItem,
    } = this.state;

    const locationArray = JSON.parse(selectedStoreItem);
    const location = `${locationArray.name}, ${addressCleanup(locationArray.address)}`;

    return (
      <div className="appointment-confirmation-wrapper">
        <div className="confirmation-header">
          <h4>{Drupal.t('Thank you for booking at Boots.')}</h4>
          <h5>{Drupal.t('A confirmation will be sent through email. Manage your appointments online.')}</h5>
        </div>
        <div className="confirmation-body">
          <div className="inner-header">
            <label>{Drupal.t('Appointment Summary')}</label>
            <span className="add-to-calendar" />
            <span className="print" />
          </div>
          <div className="inner-body">
            <ConfirmationItems
              item={{ label: Drupal.t('Appointment category'), value: appointmentCategory.name }}
            />
            <ConfirmationItems
              item={{ label: Drupal.t('Appointment type'), value: appointmentType.name }}
            />
            <ConfirmationItems
              item={{ label: Drupal.t('Location'), value: location }}
            />
          </div>

        </div>
        <div className="confirmation-footer">
          <button
            className="view-my-appointments-button"
            type="button"
            // onClick={() => this.handleBack('appointment-type')}
          >
            {Drupal.t('View My Appointments')}
          </button>
          <button
            className="continue-shopping"
            type="button"
            // onClick={this.handleSubmit}
          >
            {Drupal.t('Continue Shopping')}
          </button>
        </div>

      </div>
    );
  }
}
