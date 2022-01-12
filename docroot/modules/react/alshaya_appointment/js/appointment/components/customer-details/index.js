import React from 'react';
import _has from 'lodash/has';
import parse from 'html-react-parser';
import { getInputValue } from '../../../utilities/helper';
import ClientDetails from './components/client-details';
import CompanionDetails from './components/companion-details';
import { processCustomerDetails } from '../../../utilities/validate';
import { postAPICall, fetchAPIData } from '../../../utilities/api/fetchApiData';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../js/utilities/strings';
import stickyCTAButtonObserver from '../../../utilities/StickyCTA';

export default class CustomerDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');

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
      showFullScreenLoader();
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
          }), () => {
            const { appointmentId } = this.state;
            if (appointmentId) {
              const apiCompanionsUrl = `/get/companions?appointment=${appointmentId}&id=${id}`;
              const apiCompanionData = fetchAPIData(apiCompanionsUrl);
              if (apiCompanionData instanceof Promise) {
                apiCompanionData.then((response) => {
                  if (response.error === undefined && response.data !== undefined) {
                    if (response.data.length > 0) {
                      const companionData = {};
                      for (let i = 0; i < response.data.length; i++) {
                        const k = i + 1;
                        const firstname = `bootscompanion${k}name`;
                        const lastname = `bootscompanion${k}lastname`;
                        const dob = `bootscompanion${k}dob`;
                        companionData[firstname] = response.data[i].firstName;
                        companionData[lastname] = response.data[i].lastName;
                        companionData[dob] = response.data[i].dob;
                      }
                      this.setState({
                        companionData,
                        appointmentCompanion: {
                          label: response.data.length,
                          value: response.data.length,
                        },
                      });
                    }
                  }
                });
              }
            }
            removeFullScreenLoader();
          });
        });
      }
    }

    // We need a sticky button in mobile.
    if (window.innerWidth < 768) {
      stickyCTAButtonObserver();
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

    smoothScrollTo('.companion-details-item:last-child');
  };

  handleRemoveCompanion = (e) => {
    const { appointmentCompanion, companionData, appointmentId } = this.state;
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

      // Set key empty in case of edit.
      if (appointmentId) {
        const name = `bootscompanion${companionId}name`;
        const lastname = `bootscompanion${companionId}lastname`;
        const dob = `bootscompanion${companionId}dob`;
        updatedCompanionData[name] = '';
        updatedCompanionData[lastname] = '';
        updatedCompanionData[dob] = '';
      }
    }
    this.setState((prevState) => ({
      ...prevState,
      appointmentCompanion: { label: numOfCompanions, value: numOfCompanions },
      companionData: updatedCompanionData,
    }));

    smoothScrollTo('.companion-details-item:nth-last-child(2)');
  }

  bookAppointment = () => {
    const { handleSubmit } = this.props;
    const {
      selectedStoreItem,
      appointmentCategory,
      appointmentType,
      selectedSlot,
      appointmentId,
      originalTimeSlot,
      companionData,
      clientData,
    } = this.state;
    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    const channel = isMobile ? 'mobile' : 'desktop';
    const { id } = drupalSettings.alshaya_appointment.user_details;
    if (id) {
      clientData.id = id;
    }
    const params = {
      location: selectedStoreItem.locationExternalId,
      program: appointmentCategory.id,
      activity: appointmentType.value,
      duration: selectedSlot.lengthinMin,
      attendees: 1,
      start_date_time: selectedSlot.appointmentSlotTime,
      channel,
      user: id,
      companionData,
      clientData,
    };

    if (appointmentId && id !== 0) {
      params.appointment = appointmentId;
      params.originaltime = originalTimeSlot;
    }

    const apiData = postAPICall('/book-appointment', params);

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

          // Move to next step.
          removeFullScreenLoader();
          handleSubmit();
        } else {
          this.setState((prevState) => ({
            ...prevState,
            bookingStatus: 'failed',
          }));
          removeFullScreenLoader();
        }
      });
    }
  }

  handleSubmit = async (e) => {
    e.preventDefault();
    showFullScreenLoader();
    // Validate form fields.
    const isError = await processCustomerDetails(e);
    if (!isError) {
      Drupal.addItemInLocalStorage(
        'appointment_data',
        this.state,
        drupalSettings.alshaya_appointment.local_storage_expire * 60,
      );
      // Book appointment.
      this.bookAppointment();
    } else {
      removeFullScreenLoader();
    }
    smoothScrollTo('#appointment-booking');
  }

  handleBack = (step) => {
    const { handleBack } = this.props;
    handleBack(step);
    smoothScrollTo('#appointment-booking');
  }

  render() {
    const {
      clientData,
      companionData,
      appointmentCompanion,
    } = this.state;

    const {
      customer_details_disclaimer_text: customerDisclaimer,
    } = drupalSettings.alshaya_appointment;

    const disclaimerText = customerDisclaimer !== undefined ? parse(customerDisclaimer) : '';

    const { id } = drupalSettings.alshaya_appointment.user_details;
    const backStep = (id > 0) ? 'select-time-slot' : 'select-login-guest';

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
            {disclaimerText}
          </div>
          <div className="appointment-flow-action">
            <button
              className="customer-details-button appointment-type-button"
              type="submit"
            >
              {getStringMessage('book_an_appointment_button')}
            </button>
          </div>
          <div id="appointment-bottom-sticky-edge" />
          <div className="appointment-store-buttons-wrapper fadeInUp">
            <button
              className="appointment-type-button appointment-store-button back"
              type="button"
              onClick={() => this.handleBack(backStep)}
            >
              {getStringMessage('back')}
            </button>
          </div>
        </form>
      </div>
    );
  }
}
