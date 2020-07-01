import React from 'react';
import {getStorageInfo, setStorageInfo} from "../../../utilities/storage";
import AppointmentSlots from "../appointment-selectslot";
import fetchAPIData from '../../../utilities/api/fetchApiData';
import moment from "moment";
import AppointmentCalendar from "../appointment-calendar";

const localStorageValues = getStorageInfo();

export default class AppointmentTimeSlot extends React.Component {
  constructor(props) {
    super(props);
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
      if (localStorageValues.hasOwnProperty('date')) {
        this.state.date = new Date(localStorageValues.date);
      }
      else {
        this.state.date = new Date();
      }
    }

    this.state.timeSlots = {};
    this.state.selected_slot = {};
    this.state.params = `program=${this.state.appointmentCategoryId}&activity=${this.state.appointmentType}&location=${this.state.selectedStoreItem.locationExternalId}`;
    this.dateChanged = this.dateChanged.bind(this);
    this.handler = this.handler.bind(this);
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
  }

  handler(slot) {
    this.setState({
      selected_slot: slot
    });
  }

  handleSubmit = () => {
    this.setState({ timeslot: undefined });
    setStorageInfo(this.state);
    this.props.handleSubmit();
  }

  dateChanged(d){
    this.setState({date: d},
      () => {
        const selected_date = moment(d).format('YYYY-MM-DD');
        const apiUrl = `/get/timeslots?selected_date=${selected_date}&${this.state.params}`;
        this.fetchTimeSlots(apiUrl);
      }
    );
  }

  componentDidMount() {
    const d = new Date(this.state.date);
    const selected_date = moment(d).format('YYYY-MM-DD');
    const apiUrl = `/get/timeslots?selected_date=${selected_date}&${this.state.params}`;
    this.fetchTimeSlots(apiUrl);
  }

  fetchTimeSlots = (apiUrl) => {
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined) {
          this.setState({
            timeSlots: result.data //some user
          });
        }
      });
    }
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
            {Drupal.t("Select date & time that suits you")} *
          </div>
          <div className="timeslot-latest-available">
            <p>
              {Drupal.t('The first available appointment is on !date', arg)}
            </p>
          </div>
          <div className="appointment-datepicker">
            <AppointmentCalendar
              selectDate = {this.state.date}
              dateChanged = {this.dateChanged}
            />
          </div>

          <div className="appointment-timeslots-wrapper">
            <AppointmentSlots
              items={timeSlots}
              handler = {this.handler}
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
