import React from 'react';
import _has from 'lodash/has';
import { getInputValue } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import ClientDetails from './components/client-details';
import CompanionDetails from './components/companion-details';
import { processCustomerDetails } from '../../../utilities/validate';
import { postAPICall, fetchAPIData } from '../../../utilities/api/fetchApiData';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';

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
    const {
      id, email, fname: firstName, lname: lastName, mobile,
    } = drupalSettings.alshaya_appointment.user_details;
    let clientData = {
      email,
      firstName,
      lastName,
      mobile,
    };

    if (id) {
      const apiUrl = `/get/client?id=${id}`;
      const apiData = fetchAPIData(apiUrl);

      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            if (result.data.length !== 0) {
              clientData = result.data;
            }
          }
          this.setState((prevState) => ({
            ...prevState,
            clientData,
          }));
        });
      }
    }
  }

  handleChange = (section, e) => {
    let value; let
      name;
    let data = [];

    if (e.type === 'date') {
      name = e.name;
      value = e.value;
    } else {
      ({ name } = e.target);
      value = getInputValue(e);
    }

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

  handleAddCompanion = () => {
    const { appointmentCompanion } = this.state;
    const numOfCompanions = appointmentCompanion.value + 1;
    if (numOfCompanions > drupalSettings.alshaya_appointment.appointment_companion_limit) {
      this.setState((prevState) => ({
        ...prevState,
        maxCompanionLimitReached: true,
      }));
    } else {
      this.setState((prevState) => ({
        ...prevState,
        appointmentCompanion: { label: numOfCompanions, value: numOfCompanions },
      }));
    }
  };

  handleRemoveCompanion = (e) => {
    const { appointmentCompanion, companionData } = this.state;
    const { companionId } = e.target.dataset;
    const numOfCompanions = appointmentCompanion.value - 1;
    const updatedCompanionData = {};

    if (companionData) {
      Object.entries(companionData).forEach(([key, value]) => {
        const currentKeyArray = key.split(/(\d+)/);

        // Removing the companion data for the one being deleted.
        if (companionId !== currentKeyArray[1]) {
          let newKey = key;
          if (currentKeyArray[1] > companionId) {
            newKey = currentKeyArray[0] + (currentKeyArray[1] - 1) + currentKeyArray[2];
          }
          updatedCompanionData[newKey] = value;
        }
      });
    }

    this.setState((prevState) => ({
      ...prevState,
      appointmentCompanion: { label: numOfCompanions, value: numOfCompanions },
      companionData: updatedCompanionData,
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
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
          // Move to next step.
          const { handleSubmit } = this.props;
          handleSubmit();
        }
      });
    }
  }

  bookAppointment = () => {
    const {
      selectedStoreItem,
      appointmentCategory,
      appointmentType,
      clientExternalId,
      selectedSlot,
      appointmentId,
      originalTimeSlot,
    } = this.state;
    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    const channel = isMobile ? 'mobile' : 'desktop';

    let apiUrl = `/book-appointment?location=${selectedStoreItem.locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.value}&duration=${selectedSlot.lengthinMin}&attendees=${1}&start-date-time=${selectedSlot.appointmentSlotTime}&client=${clientExternalId}&channel=${channel}`;

    const { id } = drupalSettings.alshaya_appointment.user_details;
    if (appointmentId && id !== 0) {
      apiUrl = `${apiUrl}&appointment=${appointmentId}&originaltime=${originalTimeSlot}&id=${id}`;
    }

    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
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
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
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
    smoothScrollTo('#appointment-booking');
  }

  render() {
    const {
      clientData,
      companionData,
      appointmentCompanion,
    } = this.state;

    return (
      <div className="customer-details-wrapper">
        <form
          className="appointment-customer-details-form fadeInUp"
          style={{ animationDelay: '0.4s' }}
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <ClientDetails
            handleChange={(e) => this.handleChange('clientData', e)}
            clientData={clientData}
          />
          <CompanionDetails
            handleChange={(e) => this.handleChange('companionData', e)}
            companionData={companionData}
            handleAddCompanion={() => this.handleAddCompanion()}
            handleRemoveCompanion={(e) => this.handleRemoveCompanion(e)}
            appointmentCompanion={appointmentCompanion}
          />
          <div className="disclaimer-wrapper">
            {drupalSettings.alshaya_appointment.customer_details_disclaimer_text}
          </div>
          <div className="customer-details-button-wrapper">
            <div className="appointment-flow-action">
              <button
                className="customer-details-button appointment-type-button"
                type="submit"
              >
                {Drupal.t('Book an Appointment')}
              </button>
            </div>
          </div>
        </form>
      </div>
    );
  }
}
