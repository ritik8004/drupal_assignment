import React from 'react';
import moment from 'moment';
import AppointmentSlots from '../appointment-selectslot';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import AppointmentCalendar from '../appointment-calendar';
import { getDateFormat, getDateFormattext } from '../../../utilities/helper';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../js/utilities/strings';
import stickyCTAButtonObserver from '../../../utilities/StickyCTA';
import AppointmentSelection from '../appointment-selection';
import ConditionalView from '../../../common/components/conditional-view';

export default class AppointmentTimeSlot extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
        timeSlots: {},
        notFound: '',
      };
      if (Object.prototype.hasOwnProperty.call(localStorageValues, 'selectedSlot')) {
        this.state.date = new Date(localStorageValues.selectedSlot.appointmentSlotTime);
      } else {
        this.state.date = new Date(moment().add(1, 'day'));
      }
    }

    this.dateChanged = this.dateChanged.bind(this);
    this.handler = this.handler.bind(this);
  }

  componentDidMount() {
    const { date } = this.state;
    const d = new Date(date);
    const selectedDate = moment(d).format(getDateFormat());
    const apiUrl = `/get/timeslots?selectedDate=${selectedDate}&${this.getParamsForTimeSlotApi()}`;
    showFullScreenLoader();
    this.fetchTimeSlots(apiUrl);
    // We need a sticky button in mobile.
    if (window.innerWidth < 768) {
      stickyCTAButtonObserver();
    }
  }

  handler(slot) {
    this.setState({
      selectedSlot: slot,
    });
  }

  handleSubmit = () => {
    Drupal.addItemInLocalStorage(
      'appointment_data',
      this.state,
      drupalSettings.alshaya_appointment.local_storage_expire * 60,
    );
    const { handleSubmit } = this.props;
    handleSubmit();
    smoothScrollTo('#appointment-booking');
  };

  dateChanged(d) {
    this.setState({ date: d },
      () => {
        const selectedDate = moment(d).format(getDateFormat());
        const apiUrl = `/get/timeslots?selectedDate=${selectedDate}&${this.getParamsForTimeSlotApi()}`;
        this.fetchTimeSlots(apiUrl);
      });
  }

  fetchTimeSlots = (apiUrl) => {
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined) {
          this.setState({
            timeSlots: result.data,
          });
          if (Object.keys(result.data).length === 0) {
            this.setState({
              notFound: getStringMessage('unavailable_timelsots_message'),
            });
          }
          removeFullScreenLoader();
        }
      });
    }
  }

  getParamsForTimeSlotApi() {
    const { appointmentCategory, appointmentType, selectedStoreItem } = this.state;
    const params = `program=${appointmentCategory.id}&activity=${appointmentType.value}&location=${selectedStoreItem.locationExternalId}`;
    return params;
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
    smoothScrollTo('#appointment-booking');
  };

  render() {
    const {
      date, timeSlots, notFound, selectedSlot,
    } = this.state;

    const { handleBack } = this.props;

    return (
      <div className="appointment-store-wrapper appointment-timeslot-wrapper">
        <div className="appointment-store-inner-wrapper">
          <div className="store-header appointment-subtitle fadeInUp">
            {getStringMessage('select_timeslot_label')}
            {' '}
            *
          </div>
          <div className="timeslot-latest-available fadeInUp">
            <span>
              {`${getStringMessage('first_available_slot_label')} `}
            </span>
            <span className="starting-timeslot">{Drupal.t(moment().add(1, 'day').format(getDateFormattext()))}</span>
          </div>
          <div className="appointment-datepicker fadeInUp">
            <AppointmentCalendar
              selectDate={date}
              dateChanged={this.dateChanged}
            />
          </div>

          <div className="appointment-timeslots-wrapper fadeInUp">
            <AppointmentSlots
              notFound={notFound}
              items={timeSlots}
              handler={this.handler}
            />
          </div>

          <ConditionalView condition={window.innerWidth < 768}>
            <AppointmentSelection
              handleEdit={handleBack}
            />
          </ConditionalView>

          <div className="appointment-flow-action">
            <button
              className="appointment-type-button appointment-store-button select-store"
              type="button"
              disabled={!(selectedSlot)}
              onClick={this.handleSubmit}
            >
              {getStringMessage('book_time_slot_button')}
            </button>
          </div>
          <div id="appointment-bottom-sticky-edge" />

          <div className="appointment-store-buttons-wrapper fadeInUp">
            <button
              className="appointment-type-button appointment-store-button back"
              type="button"
              onClick={() => this.handleBack('select-store')}
            >
              {getStringMessage('back')}
            </button>
          </div>
        </div>
      </div>
    );
  }
}
