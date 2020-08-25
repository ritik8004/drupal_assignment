import 'core-js/features/url-search-params';
import 'core-js/es/symbol';
import 'core-js/es/array';
import React from 'react';
import moment from 'moment';
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
import getStringMessage from '../../../../../js/utilities/strings';
import { insertParam, setMomentLocale } from '../../../utilities/helper';
// Set language for date time translation.
if (drupalSettings.path.currentLanguage !== 'en') {
  setMomentLocale(moment);
}

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
    let localStorageValues = getStorageInfo();
    const { search } = window.location;
    const params = new URLSearchParams(search);
    const appointment = params.get('appointment');
    const step = params.get('step');

    // If URL does not have step parameter, then
    // reset the localstore and load page with step parameter
    // to start from step 1.
    if (step === null) {
      removeStorageInfo();
      localStorageValues = null;
      insertParam('step', 'set');
    }

    if (localStorageValues) {
      const { appointmentId } = localStorageValues;
      // Empty localStore if user leaves edit appointment incomplete
      // and wants to book new appointment.
      if (appointmentId && appointmentId !== appointment) {
        removeStorageInfo();
        localStorageValues = null;
      }
    }

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
    this.state.appointmentRender = false;
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
          } else {
            this.setAppointmentRender();
          }
        });
      }
    } else {
      // Check if user switched language.
      const { currentLanguage } = drupalSettings.path;
      const { langcode } = this.state;
      if (langcode !== undefined && langcode !== currentLanguage) {
        this.updateLocalStoreWithTranslations();
      } else {
        this.setAppointmentRender();
      }
    }
  }

  updateLocalStoreWithTranslations = () => {
    showFullScreenLoader();
    const apiUrl = '/get/translations';
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        // If no error and we get translation from api.
        if (result.error === undefined && result.data !== undefined) {
          const translation = result.data;
          const localStorage = getStorageInfo();
          // Update localstore translation for appointment category.
          if (localStorage.appointmentCategory.name !== undefined) {
            localStorage.appointmentCategory.name = this.getTranslation(
              localStorage.appointmentCategory.name, translation,
            );
          }

          // Update localstore translation for appointment type.
          if (localStorage.appointmentType.label !== undefined) {
            localStorage.appointmentType.label = this.getTranslation(
              localStorage.appointmentType.label, translation,
            );
          }

          // Update localstore translation for appointment type list.
          if (localStorage.appointmentTypeItems !== undefined) {
            localStorage.appointmentTypeItems.forEach((item, index) => {
              localStorage.appointmentTypeItems[index] = {
                id: item.id,
                name: this.getTranslation(item.name, translation),
                description: this.getTranslation(item.description, translation),
              };
            });
          }

          // Update localstore translation for Store list.
          if (localStorage.storeList !== undefined) {
            localStorage.storeList.forEach((item, i) => {
              localStorage.storeList[i] = this.translateStore(item, translation);
            });
          }

          // Update localstore translation for seleced store.
          if (localStorage.selectedStoreItem !== undefined) {
            const { selectedStoreItem } = localStorage;
            localStorage.selectedStoreItem = this.translateStore(selectedStoreItem, translation);
          }

          localStorage.langcode = drupalSettings.path.currentLanguage;
          setStorageInfo(localStorage);
        }
        this.setAppointmentRender();
        removeFullScreenLoader();
      });
    }
  };

  getTranslation = (string, translation) => (
    (translation[string] !== undefined) ? translation[string] : string);

  translateStore = (item, translation) => {
    const store = item;
    if (store.address !== undefined) {
      const { address } = item;
      Object.keys(address).forEach((key) => {
        store.address[key] = this.getTranslation(
          address[key], translation,
        );
      });
    }

    if (item.name !== undefined) {
      store.name = this.getTranslation(item.name, translation);
    }

    if (item.storeTiming[0].day !== undefined) {
      const days = item.storeTiming[0].day.split(' - ');
      days[0] = this.getTranslation(days[0], translation);
      days[1] = this.getTranslation(days[1], translation);
      store.storeTiming[0].day = days.join(' - ');
    }

    return store;
  }

  setAppointmentRender = () => {
    this.setState({
      appointmentRender: true,
    });
  };

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
      appointmentRender: true,
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
    localStorageValues.langcode = drupalSettings.path.currentLanguage;
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
      appointmentRender,
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
        <React.Suspense fallback={<Loading loadingMessage={getStringMessage('loading_stores_placeholder')} />}>
          <AppointmentStore
            handleBack={this.handleEdit}
            handleSubmit={() => this.handleSubmit('select-time-slot')}
          />
        </React.Suspense>
      );
    } else if (appointmentStep === 'select-time-slot') {
      appointmentClasses += 'appointment-2-cols appointment-select-time-slot-container';
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
      appointmentClasses += 'appointment-2-cols appointment-customer-details-container';
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
          step={appointmentStep}
        />
      );
    }

    return (
      <div className="appointment-wrapper">
        <AppointmentSteps step={appointmentStep} />
        { appointmentRender && (
        <div className={`${appointmentClasses}`}>
          {appointmentData}
          {appointmentSelection}
        </div>
        )}
      </div>
    );
  }
}
