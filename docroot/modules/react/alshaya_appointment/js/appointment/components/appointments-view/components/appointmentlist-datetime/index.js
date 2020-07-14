import React from 'react';
import moment from 'moment';

export default class AppointmentListDateTime extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const { appointment } = this.props;

    let appointmentStartDate = '';
    if (appointment !== undefined) {
      appointmentStartDate = appointment.appointmentStartDate;
    }

    return (
      <div className="appointment-list-date-time">
        <span>{ Drupal.t('Date and Time') }</span>
        <span>{ moment(appointmentStartDate).format('dddd, Do MMMM') }</span>
        <br />
        <span>{ moment(appointmentStartDate).format('YYYY hh:mm A') }</span>
      </div>
    );
  }
}
