import React from 'react';
import _has from 'lodash/has';
import _isEmpty from 'lodash/isEmpty';
import { getInputValue } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import ClientDetails from './components/client-details';
import CompanionDetails from './components/companion-details';
import { processCustomerDetails } from '../../../utilities/validate';
import { postAPICall, fetchAPIData } from '../../../utilities/api/fetchApiData';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

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
          });
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

  appendAppointmentAnswers = () => {
    const { handleSubmit } = this.props;
    const {
      companionData, bookingId,
    } = this.state;

    if (_isEmpty(companionData)) {
      // Move to next step.
      removeFullScreenLoader();
      handleSubmit();
      return;
    }

    const data = { ...companionData, bookingId };
    const apiUrl = '/append-appointmnet-answers';
    const apiData = postAPICall(apiUrl, data);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
          // Move to next step.
          removeFullScreenLoader();
          // Remove empty keys from companionData.
          Object.entries(companionData).forEach(([key, value]) => {
            if (!value) {
              delete companionData[key];
            }
          });
          this.setState({
            ...companionData,
          }, () => {
            setStorageInfo(this.state);
          });
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
      appointmentCompanion,
      clientExternalId,
      selectedSlot,
      appointmentId,
      originalTimeSlot,
    } = this.state;
    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    const channel = isMobile ? 'mobile' : 'desktop';
    const { id } = drupalSettings.alshaya_appointment.user_details;
    const params = {
      location: selectedStoreItem.locationExternalId,
      program: appointmentCategory.id,
      activity: appointmentType.value,
      duration: selectedSlot.lengthinMin,
      attendees: appointmentCompanion.value,
      start_date_time: selectedSlot.appointmentSlotTime,
      client: clientExternalId,
      channel,
      user: id,
    };

    if (appointmentId && id !== 0) {
      params.appointment = appointmentId;
      params.id = id;
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
          // Append appointment answers.
          this.appendAppointmentAnswers();
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

  updateInsertClient = () => {
    const {
      clientData,
    } = this.state;

    const { id } = drupalSettings.alshaya_appointment.user_details;
    if (id) {
      clientData.id = id;
    }
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
        } else {
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
      setStorageInfo(this.state);
      // Update/Insert client and then book appointment.
      this.updateInsertClient();
    } else {
      removeFullScreenLoader();
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
