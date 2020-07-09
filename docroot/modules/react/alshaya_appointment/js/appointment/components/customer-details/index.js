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
    const { email } = window.drupalSettings.alshaya_appointment.user_details;

    if (email) {
      const apiUrl = `/get/client?email=${email}`;
      const apiData = fetchAPIData(apiUrl);

      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            if (result.data.length > 0) {
              this.setState((prevState) => ({
                ...prevState,
                clientData: result.data,
              }));
            }
          }
        });
      }
    }
  }

  handleChange = (section, e) => {
    const { name } = e.target;
    const value = getInputValue(e);
    let data = [];

    if (_has(this.state, section)) {
      ({ [section]: data } = this.state);
      data[name] = value;
    } else {
      data[name] = value;
    }

    this.setState((prevState) => ({
      ...prevState,
      [section]: { ...data },
    }));
  }

  appendAppointmentAnswers = () => {
    const {
      companionData, bookingId,
    } = this.state;

    const data = { ...companionData, bookingId };
    const apiUrl = '/append-appointmnet-answers';
    const apiData = postAPICall(apiUrl, data);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          // Move to next step.
          const { handleSubmit } = this.props;
          handleSubmit();
        }
      });
    }
  }

  bookAppointment = () => {
    const {
      selectedStoreItem, appointmentCategory, appointmentType, clientExternalId, selectedSlot,
    } = this.state;
    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    const channel = isMobile ? 'mobile' : 'desktop';

    const apiUrl = `/book-appointment?location=${JSON.parse(selectedStoreItem).locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.value}&duration=${selectedSlot.lengthinMin}&attendees=${1}&start-date-time=${selectedSlot.appointmentSlotTime}&client=${clientExternalId}&channel=${channel}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            bookingId: result.data,
            bookingStatus: 'success',
          }));
          // Append appointment answers.
          this.appendAppointmentAnswers();
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
    }
  }

  render() {
    const { clientData, companionData } = this.state;

    return (
      <div className="customer-details-wrapper">
        <form
          className="appointment-customer-details-form"
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <ClientDetails
            handleChange={(e) => this.handleChange('clientData', e)}
            clientData={clientData}
          />
          <CompanionDetails
            handleChange={(e) => this.handleChange('companionData', e)}
            companionData={companionData}
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
