import React from 'react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import ConditionalView from '../../../common/components/conditional-view';
import AppointmentListItem from './components/appointmentlist';

export default class AppointmentsView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      clientData: '',
      appointments: {},
      notFound: '',
    };
    this.cancelAppointment = this.cancelAppointment.bind(this);
  }

  componentDidMount() {
    const { id, email } = drupalSettings.alshaya_appointment.user_details;

    if (id && email) {
      const apiUrl = `/get/client?email=${email}&id=${id}`;
      const apiData = fetchAPIData(apiUrl);

      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              clientData: result.data,
            }, () => {
              this.getUserAppointments();
            });
            if (result.data.length === 0) {
              this.setState({
                notFound: this.getNotFoundText(),
              });
            }
          }
        });
      }
    }
  }

  shouldComponentUpdate(nextProps, nextState) {
    return nextState;
  }

  getUserAppointments = () => {
    const { clientData } = this.state;
    const { clientExternalId } = clientData;
    const { id } = drupalSettings.alshaya_appointment.user_details;
    if (clientExternalId) {
      const apiUrl = `/get/appointments?id=${id}&client=${clientExternalId}`;
      const apiData = fetchAPIData(apiUrl);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              appointments: result.data.return.appointments,
            });
            if (result.data.return.appointments[0] === null) {
              this.setState({
                notFound: this.getNotFoundText(),
              });
            }
          }
        });
      }
    }
  };

  getNotFoundText = () => (<p>{ Drupal.t('No appointments booked.') }</p>);

  cancelAppointment = (appointmentId, index) => {
    const { appointments } = this.state;
    const appointment = appointments[index];
    const confirmText = Drupal.t('Are you sure you want to cancel appointment for !type?',
      { '!type': appointment.activityName });
    if (window.confirm(confirmText)) { // eslint-disable-line no-alert
      const { id } = drupalSettings.alshaya_appointment.user_details;
      if (id && appointmentId) {
        const apiUrl = `/cancel/appointment?id=${id}&appointment=${appointmentId}`;
        const apiData = fetchAPIData(apiUrl);
        if (apiData instanceof Promise) {
          apiData.then((result) => {
            if (result.error === undefined && result.data.return.result === 'SUCCESS') {
              appointments.splice(index, 1);
              this.setState({
                appointments,
              });
              if (appointments.length === 0) {
                this.setState({
                  notFound: this.getNotFoundText(),
                });
              }
            }
          });
        }
      }
    }
  };

  render() {
    let appointmentsRender = '';
    const { appointments, notFound } = this.state;
    const { baseUrl, pathPrefix } = drupalSettings.path;

    if (appointments.length > 0 && appointments[0] !== null) {
      appointmentsRender = appointments.map((appointment, i) => (
        <AppointmentListItem
          key={appointment.confirmationNumber}
          appointment={appointment}
          num={i}
          cancelAppointment={this.cancelAppointment}
        />
      ));
    }

    return (
      <div className="appointments-list-wrapper">
        <a href={`${baseUrl}${pathPrefix}appointment/booking`} className="appointment-booking-link-top">
          {Drupal.t('Book new appointment')}
        </a>
        <div className="appointment-list">
          {appointmentsRender}
        </div>
        <ConditionalView condition={notFound !== undefined}>
          { notFound }
        </ConditionalView>
        <a href={`${baseUrl}${pathPrefix}appointment/booking`} className="appointment-booking-link-bottom">
          {Drupal.t('Book new appointment')}
        </a>
      </div>
    );
  }
}
