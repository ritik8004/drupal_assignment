import React from 'react';
import { getInputValue } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import ClientDetails from './components/client-details';
import CompanionDetails from './components/companion-details';
import { processCustomerDetails } from '../../../utilities/validate';
import { postAPICall, fetchAPIData } from '../../../utilities/api/fetchApiData';

export default class CustomerDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  handleChange = (e) => {
    const { name } = e.target;
    const value = getInputValue(e);
    this.setState((prevState) => ({
      ...prevState,
      [name]: value,
    }));
  }

  bookAppointment = () => {
    const {
      selectedStoreItem, appointmentCategory, appointmentType, clientExternalId,
    } = this.state;
    // @TODO: Will update this code to fetch data from state once time slots code is merged.
    const duration = 90;
    const startDateTime = '2020-06-09T10:10:00.000Z';

    const apiUrl = `/book-appointment?location=${JSON.parse(selectedStoreItem).locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.id}&duration=${duration}&attendees=${1}&start-date-time=${startDateTime}&client=${clientExternalId}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            bookingId: result.data,
          }));
        }
      });
    }
  }

  updateInsertClient = () => {
    const {
      firstName, lastName, dob, email, mobile,
    } = this.state;
    const clientExternalId = this.state.clientExternalId ? this.state.clientExternalId : '';
    const data = {
      clientExternalId,
      firstName,
      lastName,
      dob,
      email,
      mobile,
    };
    const apiUrl = '/update-insert-client';
    const apiData = postAPICall(apiUrl, data);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            clientExternalId: result.data,
          }));
          console.log(result.data);

          // Book appointment using the clientExternalId.
          this.bookAppointment();
        }
      });
    }
  }

  handleSubmit = async (e) => {
    e.preventDefault();
    // Valid form fields.
    const isError = await processCustomerDetails(e);

    if (!isError) {
      setStorageInfo(this.state);
      // Update/Insert client and then book appointment.
      this.updateInsertClient();
      const { handleSubmit } = this.props;
      handleSubmit();
    }
  }

  render() {
    return (
      <div className="customer-details-wrapper">
        <form
          className="appointment-customer-details-form"
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <ClientDetails
            handleChange={this.handleChange}
          />
          <CompanionDetails
            handleChange={this.handleChange}
          />
          <div className="disclaimer-wrapper">
            {drupalSettings.alshaya_appointment.customer_details_disclaimer_text}
          </div>
          <div className="customer-details-buttons-wrapper">
            <button
              className="customer-details-button"
              type="submit"
            >
              {Drupal.t('Book Appointment')}
            </button>
          </div>
        </form>
      </div>
    );
  }
}
