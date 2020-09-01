import React from 'react';
import moment from 'moment';
import { fetchAPIData, postAPICall } from '../../../utilities/api/fetchApiData';
import ConditionalView from '../../../common/components/conditional-view';
import AppointmentListItem from './components/appointmentlist';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../js/utilities/strings';
import { setMomentLocale } from '../../../utilities/helper';
// Set language for date time translation.
if (drupalSettings.path.currentLanguage !== 'en') {
  setMomentLocale(moment);
}

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
    showFullScreenLoader();
    const { id } = drupalSettings.alshaya_appointment.user_details;

    if (id) {
      const apiUrl = `/get/client?id=${id}`;
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
              removeFullScreenLoader();
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
          removeFullScreenLoader();
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              appointments: result.data.return.appointments,
            });
            if (result.data.return.appointments.length === 0) {
              this.setState({
                notFound: this.getNotFoundText(),
              });
            }
          }
        });
      }
    }
  };

  getNotFoundText = () => (<p>{ getStringMessage('no_appointments') }</p>);

  cancelAppointment = (appointmentId, index) => {
    const { appointments } = this.state;
    const { id } = drupalSettings.alshaya_appointment.user_details;
    if (id && appointmentId) {
      const apiUrl = '/cancel/appointment';
      const data = {
        appointment: appointmentId,
        id,
      };
      showFullScreenLoader();
      const apiData = postAPICall(apiUrl, data);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          removeFullScreenLoader();
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
  };

  render() {
    let appointmentsRender = '';
    const { appointments, notFound, clientData } = this.state;
    const { baseUrl, pathPrefix } = drupalSettings.path;

    if (appointments.length > 0 && appointments[0] !== null) {
      appointmentsRender = appointments.map((appointment, i) => (
        <AppointmentListItem
          key={appointment.confirmationNumber}
          appointment={appointment}
          num={i}
          cancelAppointment={this.cancelAppointment}
          clientData={clientData}
        />
      ));
    }

    return (
      <div className="appointments-list-wrapper">
        <a
          href={`${baseUrl}${pathPrefix}appointment/booking`}
          className="appointment-booking-link-top fadeInUp"
        >
          {getStringMessage('book_new_appointment_label')}
        </a>
        <div className="appointment-list">
          {appointmentsRender}
        </div>
        <ConditionalView condition={notFound !== undefined}>
          { notFound }
        </ConditionalView>
        <div className="book-appointment-btn fadeInUp">
          <a href={`${baseUrl}${pathPrefix}appointment/booking`} className="appointment-booking-link-bottom">
            {getStringMessage('book_new_appointment_label')}
          </a>
        </div>
      </div>
    );
  }
}
