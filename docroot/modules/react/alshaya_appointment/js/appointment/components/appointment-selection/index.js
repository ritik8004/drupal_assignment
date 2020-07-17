import React from 'react';
import moment from 'moment';
import { getStorageInfo } from '../../../utilities/storage';
import StoreAddress from '../appointment-store/components/store-address';
import SectionTitle from '../section-title';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';

export default class AppointmentSelection extends React.Component {
  handleEdit = (step) => {
    const { handleEdit } = this.props;
    handleEdit(step);
    smoothScrollTo('#appointment-booking');
  }

  render() {
    const localStorageValues = getStorageInfo();
    const {
      appointmentCategory, appointmentType, selectedStoreItem, selectedSlot,
    } = localStorageValues;

    return (
      <div className="appointment-details">
        <div
          className="appointment-details-header appointment-subtitle fadeInUp"
          style={{ animationDelay: '0.4s' }}
        >
          {Drupal.t('You have chosen')}
        </div>
        <div className="appointment-details-body">
          <div
            className="appointment-details-wrapper appointment-type-details-wrapper fadeInUp"
            style={{ animationDelay: '0.6s' }}
          >
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
                {appointmentType && appointmentType.label}
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
              <div
                className="appointment-details-wrapper appointment-store-details-wrapper fadeInUp"
                style={{ animationDelay: '0.8s' }}
              >
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
              <div
                className="appointment-details-wrapper appointment-timeslot-details-wrapper fadeInUp"
                style={{ animationDelay: '1s' }}
              >
                <div className="appointment-details-item">
                  <div className="appointment-details-item-header">
                    <SectionTitle>{Drupal.t('Date')}</SectionTitle>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      { moment(selectedSlot.appointmentSlotTime).format('dddd, Do MMMM YYYY') }
                    </div>
                  </div>
                </div>
                <div className="appointment-details-item">
                  <div className="appointment-details-item-header">
                    <SectionTitle>{Drupal.t('Time')}</SectionTitle>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      { moment(selectedSlot.appointmentSlotTime).format('LT') }
                    </div>
                  </div>
                </div>
                <button
                  className="appointment-details-button edit-button"
                  type="button"
                  onClick={() => this.handleEdit('select-time-slot')}
                >
                  {Drupal.t('Edit')}
                </button>
              </div>
            )
            : null}

        </div>
      </div>
    );
  }
}
