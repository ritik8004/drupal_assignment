import React from 'react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import AppointmentlistType from './components/appointmentlist-type';
import AppointmentListDateTime from './components/appointmentlist-datetime';
import AppointmentListStore from './components/appointmentlist-store';
import AppointmentListCompanions from './components/appointmentlist-companion';

export default class AppointmentsView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      clientData: '',
      appointments: {},
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
          }
        });
      }
    }
  }

  render() {
    let appointmentsRender = '';
    const { appointments } = this.state;
    const { baseUrl, pathPrefix } = drupalSettings.path;
    if (appointments.length > 0) {
      appointmentsRender = appointments.map((appointment) => (
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
            <a href="#" className="action-edit">{Drupal.t('Edit')}</a>
            <a href="#" className="action-delete">{Drupal.t('Delete')}</a>
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
        <a href={`${baseUrl}${pathPrefix}appointment/booking`} className="appointment-booking-link-bottom">
          {Drupal.t('Book new appointment')}
        </a>
      </div>
    );
  }
}
