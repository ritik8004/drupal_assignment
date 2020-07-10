import React from 'react';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';

export default class AppointmentsView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      clientData: '',
      appointments: 'Placeholder',
    };
  }

  componentDidMount() {
    const { email } = window.drupalSettings.alshaya_appointment.user_details;

    if (email) {
      const apiUrl = `/get/client?email=${email}`;
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

  }

  render() {
    const { appointments, clientData } = this.state;
    return (
      <div className="appointments-list-wrapper">
        <p>
          { appointments }
          { clientData.clientExternalId }
        </p>
      </div>
    );
  }
}
