import React from 'react';
import moment from 'moment';
import { getStorageInfo } from '../../../utilities/storage';
import StoreAddress from '../appointment-store/components/store-address';
import SectionTitle from '../section-title';

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
    const {
      appointmentCategory, appointmentType, selectedStoreItem, selectedSlot,
    } = this.state;

    return (
      <div className="appointment-details">
        <div className="appointment-details-header appointment-subtitle">
          {Drupal.t('You have chosen')}
        </div>
        <div className="appointment-details-body">
          <div className="appointment-type-details-wrapper">
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <SectionTitle>
                  {Drupal.t('Appointment category')}
                </SectionTitle>
              </div>
              <div className="appointment-details-item-body">
                {appointmentCategory && appointmentCategory.name}
              </div>
            </div>
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <SectionTitle>
                  {Drupal.t('Appointment type')}
                </SectionTitle>
              </div>
              <div className="appointment-details-item-body">
                {appointmentType && appointmentType.name}
              </div>
            </div>
            <button
              className="appointment-details-button edit-button"
              type="button"
              onClick={() => this.handleEdit('appointment-type')}
            >
              {Drupal.t('Edit')}
            </button>
          </div>

          { selectedStoreItem
            ? (
              <div className="appointment-store-details-wrapper">
                <div className="appointment-details-item">
                  <div className="appointment-details-item-header">
                    <SectionTitle>
                      {Drupal.t('Location')}
                    </SectionTitle>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      {selectedStoreItem.name}
                    </div>
                    <StoreAddress
                      address={selectedStoreItem.address}
                    />
                  </div>
                </div>
                <button
                  className="appointment-details-button edit-button"
                  type="button"
                  onClick={() => this.handleEdit('select-store')}
                >
                  {Drupal.t('Edit')}
                </button>
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
            : null}

        </div>
      </div>
    );
  }
}
