import 'core-js/features/url-search-params';
import 'core-js/es/symbol';
import 'core-js/es/array';
import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import Loading from '../../../utilities/loading';
import AppointmentSelection from '../appointment-selection';
import CustomerDetails from '../customer-details';
import Confirmation from '../confirmation';
import {
  setStorageInfo,
  getStorageInfo,
  removeStorageInfo,
} from '../../../utilities/storage';
import AppointmentTimeSlot from '../appointment-timeslot';
import AppointmentLogin from '../appointment-login';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import AppointmentMessages from '../appointment-messages';

const AppointmentStore = React.lazy(async () => {
  // Wait for google object to load.
  await new Promise((resolve) => {
    const interval = setInterval(() => {
      if (typeof google !== 'undefined') {
        clearInterval(interval);
        resolve();
      }
    }, 500);
  });
  return import('../appointment-store');
});

export default class Appointment extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
      const userId = drupalSettings.alshaya_appointment.user_details.id;
      if (userId !== 0 && localStorageValues.appointmentStep === 'select-login-guest') {
        this.state.appointmentStep = 'customer-details';
        localStorageValues.appointmentStep = 'customer-details';
        setStorageInfo(localStorageValues);
      }
    } else {
      this.state = {
        appointmentStep: 'appointment-type',
      };
    }
  }

  /**
   * Get Appointment and client details is query string has appointment id.
   */
  componentDidMount() {
    const { search } = window.location;
    const params = new URLSearchParams(search);
    const appointment = params.get('appointment');
    const step = params.get('step');
    const { id } = drupalSettings.alshaya_appointment.user_details;

    if (id && appointment && step) {
      const apiUrl = `/get/client?id=${id}`;
      const apiData = fetchAPIData(apiUrl);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            const clientData = result.data;
            const apiUrlAppointment = `/get/appointment-details?appointment=${appointment}&id=${id}`;
            const apiDataAppointment = fetchAPIData(apiUrlAppointment);
            showFullScreenLoader();

            if (apiDataAppointment instanceof Promise) {
              apiDataAppointment.then((response) => {
                if (response.error === undefined && response.data !== undefined) {
                  if (Object.prototype.hasOwnProperty.call(response.data.return, 'appointment')) {
                    this.validateAppointmentEdit(clientData, response.data.return.appointment);
                  }
                } else {
                  const { baseUrl, pathPrefix } = drupalSettings.path;
                  removeStorageInfo();
                  window.location.replace(`${baseUrl}${pathPrefix}appointment/booking`);
                }
              });
            }
          }
        });
      }
    }
  }

  /**
   * Validates appointment edit permission.
   */
  validateAppointmentEdit(client, appointment) {
    if (client.clientExternalId !== appointment.clientExternalId) {
      const { baseUrl, pathPrefix } = drupalSettings.path;
      removeStorageInfo();
      window.location.replace(`${baseUrl}${pathPrefix}appointment/booking`);
    }

    const apiUrl = `/get/store/criteria?location=${appointment.locationExternalId}`;
    const apiData = fetchAPIData(apiUrl);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          const locationInfo = result.data.return.locations;
          this.prepareLocalStoreforEdit(client, appointment, locationInfo);
        }
      });
    }
  }

  /**
   * Prepare localstorage for appointment edit.
   */
  prepareLocalStoreforEdit(client, appointment, locationInfo) {
    const { search } = window.location;
    const params = new URLSearchParams(search);
    const step = params.get('step');
    const steps = [
      'select-store',
      'select-time-slot',
      'customer-details',
    ];
    if (!steps.includes(step)) {
      const { baseUrl, pathPrefix } = drupalSettings.path;
      const { id } = drupalSettings.alshaya_appointment.user_details;
      window.location.replace(`${baseUrl}${pathPrefix}user/${id}/appointments`);
    }

    const storeInfo = {
      locationExternalId: locationInfo.locationExternalId,
      name: locationInfo.locationName,
      address: locationInfo.companyAddress,
      lat: locationInfo.geocoordinates.latitude,
      lng: locationInfo.geocoordinates.longitude,
      storeTiming: [],
    };

    const listItems = drupalSettings.alshaya_appointment.appointment_companion_limit;
    const companionItems = [...Array(parseInt(listItems, 10))]
      .map((e, i) => ({ value: i + 1, label: i + 1 }));

    const localstore = {
      appointmentCategory: {
        id: appointment.programExternalId,
        name: appointment.programName,
      },
      appointmentType: {
        value: appointment.activityExternalId,
        label: appointment.activityName,
      },
      appointmentCompanion: {
        value: appointment.numberOfAttendees,
        label: appointment.numberOfAttendees,
      },
      appointmentCompanionItems: companionItems,
      appointmentStep: step,
      selectedStoreItem: storeInfo,
      selectedSlot: {
        appointmentSlotTime: appointment.appointmentStartDate,
        lengthinMin: appointment.appointmentDurationMin,
        resourceExternalIds: appointment.resourceExternalId,
      },
      storeList: [],
      originalTimeSlot: appointment.appointmentStartDate,
      appointmentId: appointment.confirmationNumber,
    };
    removeStorageInfo();
    setStorageInfo(localstore);
    this.setState({
      ...localstore,
    });
    removeFullScreenLoader();
  }

  handleSubmit = (stepValue) => {
    let stepval = stepValue;
    const userId = drupalSettings.alshaya_appointment.user_details.id;
    if (userId !== 0 && stepValue === 'select-login-guest') {
      stepval = 'customer-details';
    }

    const localStorageValues = getStorageInfo();

    localStorageValues.appointmentStep = stepval;
    setStorageInfo(localStorageValues);

    this.setState((prevState) => ({
      ...prevState,
      appointmentStep: stepval,
    }));
  }

  handleEdit = (step) => {
    this.setState({
      appointmentStep: step,
    });
  }

  render() {
    const {
      appointmentStep,
    } = this.state;

    let appointmentData;
    let appointmentSelection;
    let appointmentClasses = 'appointment-inner-wrapper ';

    if (appointmentStep === 'appointment-type') {
      appointmentData = (
        <AppointmentType
          handleSubmit={() => this.handleSubmit('select-store')}
        />
      );
    } else if (appointmentStep === 'select-store') {
      appointmentClasses += 'appointment-2-cols appointment-select-store-container';
      appointmentData = (
        <React.Suspense fallback={<Loading loadingMessage={Drupal.t('Loading Stores')} />}>
          <AppointmentStore
            handleBack={this.handleEdit}
            handleSubmit={() => this.handleSubmit('select-time-slot')}
          />
        </React.Suspense>
      );
    } else if (appointmentStep === 'select-time-slot') {
      appointmentClasses += 'appointment-2-cols';
      appointmentData = (
        <AppointmentTimeSlot
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('select-login-guest')}
        />
      );
    } else if (appointmentStep === 'select-login-guest') {
      appointmentClasses += 'appointment-2-cols appointment-login-guest-container';
      appointmentData = (
        <AppointmentLogin
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('customer-details')}
        />
      );
    } else if (appointmentStep === 'customer-details') {
      appointmentClasses += 'appointment-2-cols';
      appointmentData = (
        <CustomerDetails
          handleSubmit={() => this.handleSubmit('confirmation')}
        />
      );
    } else if (appointmentStep === 'confirmation') {
      appointmentData = (
        <Confirmation />
      );
    }

    if (appointmentStep !== 'appointment-type' && appointmentStep !== 'confirmation') {
      appointmentSelection = (
        <AppointmentSelection
          handleEdit={this.handleEdit}
        />
      );
    }

    return (
      <div className="appointment-wrapper">
        <AppointmentSteps step={appointmentStep} />
        <AppointmentMessages />
        <div className={`${appointmentClasses}`}>
          {appointmentData}
          {appointmentSelection}
        </div>
      </div>
    );
  }
}
