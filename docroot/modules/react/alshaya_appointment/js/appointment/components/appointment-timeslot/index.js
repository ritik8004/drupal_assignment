import React from 'react';
import moment from 'moment';
import { getStorageInfo, setStorageInfo } from '../../../utilities/storage';
import AppointmentSlots from '../appointment-selectslot';
import fetchAPIData from '../../../utilities/api/fetchApiData';
import AppointmentCalendar from '../appointment-calendar';

const localStorageValues = getStorageInfo();

export default class AppointmentTimeSlot extends React.Component {
  constructor(props) {
    super(props);
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
        selectedSlot: {},
        timeSlots: {},
      };
      if (Object.prototype.hasOwnProperty.call(localStorageValues, 'date')) {
        this.state.date = new Date(localStorageValues.date);
      } else {
        this.state.date = new Date();
      }
    }

    this.dateChanged = this.dateChanged.bind(this);
    this.handler = this.handler.bind(this);
  }

  componentDidMount() {
    const { date } = this.state;
    const d = new Date(date);
    const selectedDate = moment(d).format('YYYY-MM-DD');
    const apiUrl = `/get/timeslots?selectedDate=${selectedDate}&${this.getParamsForTimeSlotApi()}`;
    this.fetchTimeSlots(apiUrl);
  }

  handler(slot) {
    this.setState({
      selectedSlot: slot,
    });
  }

  handleSubmit = () => {
    const { handleSubmit } = this.props;
    setStorageInfo(this.state);
    handleSubmit();
  }

  dateChanged(d) {
    this.setState({ date: d },
      () => {
        const selectedDate = moment(d).format('YYYY-MM-DD');
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
            timeSlots: result.data, // some user
          });
        }
      });
    }
  }

  getParamsForTimeSlotApi() {
    const { appointmentCategoryId, appointmentType, selectedStoreItem } = this.state;
    const params = `program=${appointmentCategoryId}&activity=${appointmentType}&location=${selectedStoreItem.locationExternalId}`;
    return params;
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
  }

  render() {
    const { date, timeSlots } = this.state;
    const arg = {
      '!date': moment().format('dddd DD MMMM'),
    };
    return (
      <div className="appointment-store-wrapper">
        <div className="appointment-store-inner-wrapper">
          <div className="store-header">
            {Drupal.t('Select date & time that suits you')}
            {' '}
            *
          </div>
          <div className="timeslot-latest-available">
            <p>
              {Drupal.t('The first available appointment is on !date', arg)}
            </p>
          </div>
          <div className="appointment-datepicker">
            <AppointmentCalendar
              selectDate={date}
              dateChanged={this.dateChanged}
            />
          </div>

          <div className="appointment-timeslots-wrapper">
            <AppointmentSlots
              items={timeSlots}
              handler={this.handler}
            />
          </div>

          <div className="appointment-store-buttons-wrapper">
            <button
              className="appointment-store-button back"
              type="button"
              onClick={() => this.handleBack(1)}
            >
              {Drupal.t('BACK')}
            </button>
            <button
              className="appointment-store-button select-store"
              type="button"

              onClick={this.handleSubmit}
            >
              {Drupal.t('book time slot')}
            </button>
          </div>

        </div>
      </div>
    );
  }
}
