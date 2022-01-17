import React from 'react';
import moment from 'moment';
import AddToCalendar from 'react-add-to-calendar';
import ReactToPrint from 'react-to-print';
import ConfirmationItems from './components/confirmation-items';
import {
  addressCleanup,
  getArrayFromCompanionData, getTimeFormat,
} from '../../../utilities/helper';
import AppointmentConfirmationPrint from './confirmationPrint';
import getStringMessage from '../../../../../js/utilities/strings';
import stickyCTAButtonObserver from '../../../utilities/StickyCTA';
import ConditionalView from '../../../common/components/conditional-view';

export default class Confirmation extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  componentDidMount() {
    // Clear local storage.
    Drupal.removeItemFromLocalStorage('appointment_data');
    // We need a sticky button in mobile.
    if (window.innerWidth < 768) {
      stickyCTAButtonObserver();
    }
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
    const time = moment(selectedSlot.appointmentSlotTime).format(getTimeFormat());
    const location = `${selectedStoreItem.name}, ${addressCleanup(selectedStoreItem.address)}`;
    const event = {
      title: appointmentType.label,
      location,
      startTime: selectedSlot.appointmentSlotTime,
      endTime: moment(selectedSlot.appointmentSlotTime).add(selectedSlot.lengthinMin, 'minutes'),
    };

    // Construct companion array from companionData.
    const companion = getArrayFromCompanionData(companionData);

    let companionsRender = '';
    if (companion.length > 0) {
      companionsRender = companion.map((item) => (
        <ConfirmationItems
          key={item.label}
          item={{ label: item.label, value: item.value }}
        />
      ));
    }
    // As of now, translation is added as english text only.
    // We can update translations here: _alshaya_appointment_translations()
    const items = [
      { apple: getStringMessage('apple_calendar') },
      { google: getStringMessage('google') },
      { outlook: getStringMessage('outlook') },
      { outlookcom: getStringMessage('outlookdotcom') },
      { yahoo: getStringMessage('yahoo') },
    ];

    return (
      <div className="appointment-confirmation-wrapper">
        <div className="confirmation-header fadeInUp">
          <h4>{getStringMessage('confirmation_header')}</h4>
          <h5>{getStringMessage('confirmation_subheader')}</h5>
        </div>
        <div className="confirmation-body">
          <div className="inner-header fadeInUp">
            <label>{getStringMessage('appointment_summary_label')}</label>
            <div className="appointment-confirmation-option">
              <AddToCalendar
                event={event}
                buttonLabel={getStringMessage('add_to_calendar_label')}
                listItems={items}
              />
              <ReactToPrint
                trigger={() => <span className="print">{getStringMessage('print')}</span>}
                content={() => this.componentRef}
              />
            </div>
          </div>
          <div className="inner-body fadeInUp">
            <ConfirmationItems
              item={{ label: getStringMessage('appointment_booked_by_label'), value: `${clientData.firstName} ${clientData.lastName}` }}
            />
            { companionsRender }
            <ConfirmationItems
              item={{ label: getStringMessage('program_label'), value: appointmentCategory.name }}
            />
            <ConfirmationItems
              item={{ label: getStringMessage('activity_label'), value: appointmentType.label }}
            />
            <ConfirmationItems
              item={{ label: getStringMessage('location'), value: location }}
            />
            <ConfirmationItems
              item={{ label: getStringMessage('date'), value: date }}
            />
            <ConfirmationItems
              item={{ label: getStringMessage('time'), value: time }}
            />
          </div>
        </div>
        <ConditionalView condition={window.innerWidth < 768}>
          <div className="appointment-flow-action">
            <button
              className="continue-shopping"
              type="button"
              onClick={() => this.handleClick('/')}
            >
              {getStringMessage('continue_shopping_button')}
            </button>
          </div>
          <div id="appointment-bottom-sticky-edge" />
        </ConditionalView>
        <div className="confirmation-footer fadeInUp">
          { id !== 0
            && (
            <button
              className="view-my-appointments-button"
              type="button"
              onClick={() => this.handleClick(`user/${id}/appointments`)}
            >
              {getStringMessage('view_appointments_button')}
            </button>
            )}
        </div>
        <ConditionalView condition={window.innerWidth >= 768}>
          <div className="appointment-flow-action">
            <button
              className="continue-shopping"
              type="button"
              onClick={() => this.handleClick('/')}
            >
              {getStringMessage('continue_shopping_button')}
            </button>
          </div>
        </ConditionalView>
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
