import React from 'react';
import _has from 'lodash/has';
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

  componentDidMount() {
    const clientExternalId = '3C7F3390-D922-4381-BDCF-F017FAD5310F';

    if (!_has(this.state, 'clientExternalId')) {
      const apiUrl = `/get/client?client=${clientExternalId}`;
      const apiData = fetchAPIData(apiUrl);

      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState((prevState) => ({
              ...prevState,
              clientData: result.data,
            }));
          }
        });
      }
    }
  }

  handleChange = (e) => {
    const { name } = e.target;
    const value = getInputValue(e);
    const { clientData } = this.state;
    const clientDataArray = JSON.parse(JSON.stringify(clientData));
    clientDataArray[name] = value;

    this.setState((prevState) => ({
      ...prevState,
      clientData: clientDataArray,
    }));
  }

  bookAppointment = () => {
    const {
      selectedStoreItem, appointmentCategory, appointmentType, clientExternalId,
    } = this.state;
    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    const channel = isMobile ? 'mobile' : 'desktop';
    // @TODO: Will update this code to fetch data from state once time slots code is merged.
    const duration = 90;
    const startDateTime = '2020-06-09T10:10:00.000Z';

    const apiUrl = `/book-appointment?location=${JSON.parse(selectedStoreItem).locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.id}&duration=${duration}&attendees=${1}&start-date-time=${startDateTime}&client=${clientExternalId}&channel=${channel}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            bookingId: result.data,
            bookingStatus: 'success',
          }));
        } else {
          this.setState((prevState) => ({
            ...prevState,
            bookingStatus: 'failed',
          }));
        }
      });
    }
  }

  updateInsertClient = () => {
    const {
      clientData,
    } = this.state;

    let clientExternalId = '';
    if (_has(this.state, 'clientExternalId')) {
      ({
        clientExternalId,
      } = this.state);
    }

    clientData.clientExternalId = clientExternalId;

    const apiUrl = '/update-insert-client';
    const apiData = postAPICall(apiUrl, clientData);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            clientExternalId: result.data,
          }));

          // Save client id in local storage.
          setStorageInfo(this.state);

          // Book appointment using the clientExternalId.
          this.bookAppointment();
        }
      });
    }
  }

  handleSubmit = async (e) => {
    e.preventDefault();
    // Validate form fields.
    const isError = await processCustomerDetails(e);
    if (!isError) {
      setStorageInfo(this.state);
      // Update/Insert client and then book appointment.
      this.updateInsertClient();
      const { bookingStatus } = this.state;
      if (bookingStatus !== 'failed') {
        const { handleSubmit } = this.props;
        handleSubmit();
      }
    }
  }

  render() {
    const { clientData } = this.state;

    return (
      <div className="customer-details-wrapper">
        <form
          className="appointment-customer-details-form"
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <ClientDetails
            handleChange={this.handleChange}
            clientData={clientData}
          />
          <CompanionDetails
            handleChange={this.handleChange}
            clientData={clientData}
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
