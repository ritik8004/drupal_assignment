import React from 'react';
import moment from 'moment';
import AddToCalendar from 'react-add-to-calendar';
import ReactToPrint from 'react-to-print';
import ConfirmationItems from './components/confirmation-items';
import { getStorageInfo, removeStorageInfo } from '../../../utilities/storage';
import { addressCleanup } from '../../../utilities/helper';
import AppointmentConfirmationPrint from './confirmationPrint';

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

  componentDidMount() {
    // Clear localStorage.
    removeStorageInfo();
  }

  handleClick = (url) => {
    window.location.href = Drupal.url(url);
  }

  render() {
    const {
      appointmentCategory,
      appointmentType,
      selectedStoreItem,
      selectedSlot,
      clientData,
      companionData,
    } = this.state;

    const { id } = drupalSettings.alshaya_appointment.user_details;

    const date = moment(selectedSlot.appointmentSlotTime).format('dddd, Do MMMM YYYY');
    const time = moment(selectedSlot.appointmentSlotTime).format('LT');
    const location = `${selectedStoreItem.name}, ${addressCleanup(selectedStoreItem.address)}`;
    const event = {
      title: appointmentType.label,
      location,
      startTime: selectedSlot.appointmentSlotTime,
      endTime: moment(selectedSlot.appointmentSlotTime).add(selectedSlot.lengthinMin, 'minutes'),
    };

    const companion = [];
    if (companionData !== undefined) {
      // Construct companion array,
      // as companionData has individual key for each field name, lastname, dob.
      for (let i = 1; i <= parseInt(Object.keys(companionData).length / 3, 10); i++) {
        const name = `bootscompanion${i}name`;
        const lastname = `bootscompanion${i}lastname`;
        const item = {
          label: `Companion ${i}`,
          value: `${companionData[name]} ${companionData[lastname]}`,
        };
        companion.push(item);
      }
    }

    let companionsRender = '';
    if (companion.length > 0) {
      companionsRender = companion.map((item) => (
        <ConfirmationItems
          item={{ label: item.label, value: item.value }}
        />
      ));
    }

    return (
      <div className="appointment-confirmation-wrapper">
        <div className="confirmation-header fadeInUp">
          <h4>{Drupal.t('Thank you for booking at Boots.')}</h4>
          <h5>{Drupal.t('A confirmation will be sent through email. Manage your appointments online.')}</h5>
        </div>
        <div className="confirmation-body">
          <div className="inner-header fadeInUp">
            <label>{Drupal.t('Appointment Summary')}</label>
            <div className="appointment-confirmation-option">
              <AddToCalendar
                event={event}
              />
              <ReactToPrint
                trigger={() => <span className="print">{Drupal.t('Print')}</span>}
                content={() => this.componentRef}
              />
            </div>
          </div>
          <div className="inner-body fadeInUp">
            <ConfirmationItems
              item={{ label: Drupal.t('Appointment Booked by'), value: `${clientData.firstName} ${clientData.lastName}` }}
            />
            { companionsRender }
            <ConfirmationItems
              item={{ label: Drupal.t('Appointment category'), value: appointmentCategory.name }}
            />
            <ConfirmationItems
              item={{ label: Drupal.t('Appointment type'), value: appointmentType.label }}
            />
            <ConfirmationItems
              item={{ label: Drupal.t('Location'), value: location }}
            />
            <ConfirmationItems
              item={{ label: Drupal.t('Date'), value: date }}
            />
            <ConfirmationItems
              item={{ label: Drupal.t('Time'), value: time }}
            />
          </div>
        </div>
        <div className="confirmation-footer fadeInUp">
          { id !== 0
            && (
            <button
              className="view-my-appointments-button"
              type="button"
              onClick={() => this.handleClick(`user/${id}/appointments`)}
            >
              {Drupal.t('View My Appointments')}
            </button>
            )}
          <button
            className="continue-shopping"
            type="button"
            onClick={() => this.handleClick('/')}
          >
            {Drupal.t('Continue Shopping')}
          </button>
        </div>
        <div style={{ display: 'none' }} className="appointment-confirmation-print-wrapper">
          <AppointmentConfirmationPrint
            ref={(el) => { this.componentRef = el; }}
            clientData={clientData}
            appointmentCategory={appointmentCategory}
            appointmentType={appointmentType}
            location={location}
            companion={companion}
            date={date}
            time={time}
          />
        </div>
      </div>
    );
  }
}
