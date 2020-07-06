import React from 'react';
import { getStorageInfo } from '../../../utilities/storage';
import StoreAddress from '../appointment-store/components/store-address';
import moment from 'moment';

export default class AppointmentSelection extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  handleEdit = (step) => {
    const { handleEdit } = this.props;
    handleEdit(step);
  }

  render() {
    const { appointmentCategory, appointmentType, selectedStoreItem, selectedSlot } = this.state;
    const selectedStoreDetails = selectedStoreItem ? JSON.parse(selectedStoreItem) : '';

    return (
      <div className="appointment-details">
        <div className="appointment-details-header">
          {Drupal.t('You have chosen')}
        </div>
        <div className="appointment-details-body">
          <div className="appointment-type-details-wrapper">
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <label>{Drupal.t('Appointment category')}</label>
              </div>
              <div className="appointment-details-item-body">
                {appointmentCategory && appointmentCategory.name}
              </div>
            </div>
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <label>{Drupal.t('Appointment type')}</label>
              </div>
              <div className="appointment-details-item-body">
                {appointmentType && appointmentType.name}
              </div>
            </div>
            <div className="appointment-details-item edit-button">
              <button
                className="appointment-details-button"
                type="button"
                onClick={() => this.handleEdit('appointment-type')}
              >
                {Drupal.t('Edit')}
              </button>
            </div>
          </div>

          { selectedStoreDetails
            ? (
              <div className="appointment-store-details-wrapper">
                <div className="appointment-details-item">
                  <div className="appointment-details-item-header">
                    <label>{Drupal.t('Location')}</label>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      {selectedStoreDetails.name}
                    </div>
                    <StoreAddress
                      address={selectedStoreDetails.address}
                    />
                  </div>
                </div>
                <div className="appointment-details-item edit-button">
                  <button
                    className="appointment-details-button"
                    type="button"
                    onClick={() => this.handleEdit('select-store')}
                  >
                    {Drupal.t('Edit')}
                  </button>
                </div>
              </div>
            )
            : null}

          { selectedSlot
            ? (
              <div className="appointment-timeslot-details-wrapper">
                <div className="appointment-details-item">
                  <div className="appointment-details-item-header">
                    <label>{Drupal.t('Date')}</label>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      { moment(selectedSlot.appointmentSlotTime).format('dddd, Do MMMM YYYY') }
                    </div>
                  </div>
                  <div className="appointment-details-item-header">
                    <label>{Drupal.t('Time')}</label>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      { moment(selectedSlot.appointmentSlotTime).format('LT') }
                    </div>
                  </div>
                </div>
                <div className="appointment-details-item edit-button">
                  <button
                    className="appointment-details-button"
                    type="button"
                    onClick={() => this.handleEdit('select-time-slot')}
                  >
                    {Drupal.t('Edit')}
                  </button>
                </div>
              </div>
            )
            : null
          }

        </div>
      </div>
    );
  }
}
