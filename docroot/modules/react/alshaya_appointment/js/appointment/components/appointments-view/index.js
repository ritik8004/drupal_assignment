import React from 'react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import AppointmentlistType from './components/appointmentlist-type';
import AppointmentListDateTime from './components/appointmentlist-datetime';
import AppointmentListStore from './components/appointmentlist-store';
import AppointmentListCompanions from './components/appointmentlist-companion';
import ConditionalView from '../../../common/components/conditional-view';

export default class AppointmentsView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      clientData: '',
      appointments: {},
      notFound: '',
    };
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
    const confirmText = Drupal.t('Are you sure you wish to cancel this appointment?');
    if (window.confirm(confirmText)) { // eslint-disable-line no-alert
      const { id } = drupalSettings.alshaya_appointment.user_details;

      if (id && appointmentId) {
        const apiUrl = `/cancel/appointment?id=${id}&appointment=${appointmentId}`;
        const apiData = fetchAPIData(apiUrl);
        if (apiData instanceof Promise) {
          apiData.then((result) => {
            if (result.error === undefined && result.data.return.result === 'SUCCESS') {
              const { appointments } = this.state;
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
        <>
          <div className="appointment-type">
            <AppointmentlistType appointment={appointment} />
          </div>
          <div className="appointment-date-time">
            <AppointmentListDateTime appointment={appointment} />
          </div>
          <div className="appointment-store">
            <AppointmentListStore appointment={appointment} />
          </div>
          <div className="appointment-companions">
            <AppointmentListCompanions appointment={appointment} />
          </div>
          <div className="appointment-actions">
            <button type="button" className="action-edit">{Drupal.t('Edit')}</button>
            <button
              type="button"
              className="action-delete"
              onClick={() => this.cancelAppointment(appointment.confirmationNumber, i)}
            >
              {Drupal.t('Delete')}
            </button>
          </div>
        </>
      ));
    }

    return (
      <div className="appointments-list-wrapper">
        <a href={`${baseUrl}${pathPrefix}appointment/booking`} className="appointment-booking-link-top">
          {Drupal.t('Book new appointment')}
        </a>
        {appointmentsRender}
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
