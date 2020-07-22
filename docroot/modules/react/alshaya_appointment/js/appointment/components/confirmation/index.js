import React from 'react';
import moment from 'moment';
import AddToCalendar from 'react-add-to-calendar';
import ConfirmationItems from './components/confirmation-items';
import { getStorageInfo, removeStorageInfo } from '../../../utilities/storage';
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
              <span
                className="print"
                onClick={() => window.print()}
              >
                {Drupal.t('Print')}
              </span>
            </div>
          </div>
          <div className="inner-body fadeInUp">
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
      </div>
    );
  }
}
