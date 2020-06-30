import React from 'react';
import {getStorageInfo, setStorageInfo} from "../../../utilities/storage";
import DatePicker from "react-datepicker/es";
import 'react-datepicker/dist/react-datepicker.css';
import AppointmentSlots from "../appointment-selectslot";
import fetchAPIData from '../../../utilities/api/fetchApiData';
import moment from "moment";

const localStorageValues = getStorageInfo();

export default class AppointmentTimeSlot extends React.Component {
  constructor(props) {
    super(props);
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
      this.state.date = new Date(this.state.date);
    }
    else {
      this.state.date = new Date();
    }
    this.state.timeSlots = {};
    this.state.selected_slot = {};
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
        const apiUrl = `/get/timeslots?selected_date=${selected_date}&program=${this.state.appointmentCategory}&activity=${this.state.appointmentType}&location=UAE-DBX-100001`;
        this.fetchTimeSlots(apiUrl);
      }
    );
  }

  componentDidMount() {
    const d = new Date();
    const selected_date = moment(d).format('YYYY-MM-DD');
    const apiUrl = `/get/timeslots?selected_date=${selected_date}&program=${this.state.appointmentCategory}&activity=${this.state.appointmentType}&location=UAE-DBX-100001`;
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

    return (
      <div className="appointment-store-wrapper">
        <div className="appointment-store-inner-wrapper">
          <div className="store-header">
            {Drupal.t("Select date & time that suits you")} *
          </div>

          <div className="appointment-datepicker">
            <DatePicker
              selected={date}
              onChange={this.dateChanged}
              inline
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
