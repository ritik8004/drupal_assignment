import React from 'react';
import moment from 'moment';
import StoreAddress from '../appointment-store/components/store-address';
import SectionTitle from '../section-title';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';
import getStringMessage from '../../../../../js/utilities/strings';
import { getTimeFormat } from '../../../utilities/helper';

export default class AppointmentSelection extends React.Component {
  handleEdit = (step) => {
    const { handleEdit } = this.props;
    handleEdit(step);
    smoothScrollTo('#appointment-booking');
  }

  render() {
    const { step } = this.props;
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');
    const {
      appointmentCategory, appointmentType, selectedStoreItem, selectedSlot, appointmentId,
    } = localStorageValues;

    return (
      <div className="appointment-details">
        <div
          className="appointment-details-header appointment-subtitle fadeInUp"
          style={{ animationDelay: '0.4s' }}
        >
          {getStringMessage('selection_header')}
        </div>
        <div className="appointment-details-body">
          <div
            className="appointment-details-wrapper appointment-type-details-wrapper fadeInUp"
            style={{ animationDelay: '0.6s' }}
          >
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <SectionTitle>
                  {getStringMessage('program_label')}
                </SectionTitle>
              </div>
              <div className="appointment-details-item-body">
                {appointmentCategory && appointmentCategory.name}
              </div>
            </div>
            <div className="appointment-details-item">
              <div className="appointment-details-item-header">
                <SectionTitle>
                  {getStringMessage('activity_label')}
                </SectionTitle>
              </div>
              <div className="appointment-details-item-body">
                {appointmentType && appointmentType.label}
              </div>
            </div>
            { !appointmentId
              && (
              <button
                className="appointment-details-button edit-button"
                type="button"
                disabled={(step === 'appointment-type')}
                onClick={() => this.handleEdit('appointment-type')}
              >
                {getStringMessage('edit')}
              </button>
              )}
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
                      {getStringMessage('location')}
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
                  disabled={(step === 'select-store')}
                  onClick={() => this.handleEdit('select-store')}
                >
                  {getStringMessage('edit')}
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
                    <SectionTitle>{getStringMessage('date')}</SectionTitle>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      { moment(selectedSlot.appointmentSlotTime).format('dddd, Do MMMM YYYY') }
                    </div>
                  </div>
                </div>
                <div className="appointment-details-item">
                  <div className="appointment-details-item-header">
                    <SectionTitle>{getStringMessage('time')}</SectionTitle>
                  </div>
                  <div className="appointment-details-item-body">
                    <div className="store-name">
                      { moment(selectedSlot.appointmentSlotTime).format(getTimeFormat()) }
                    </div>
                  </div>
                </div>
                <button
                  className="appointment-details-button edit-button"
                  type="button"
                  disabled={(step === 'select-time-slot')}
                  onClick={() => this.handleEdit('select-time-slot')}
                >
                  {getStringMessage('edit')}
                </button>
              </div>
            )
            : null}

        </div>
      </div>
    );
  }
}
