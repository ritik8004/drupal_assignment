import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import Loading from '../../../utilities/loading';
import AppointmentSelection from '../appointment-selection';
import CustomerDetails from '../customer-details';
import Confirmation from '../confirmation';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import AppointmentTimeSlot from '../appointment-timeslot';
import AppointmentLogin from '../appointment-login';

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
        <AppointmentSteps />
        <div className={`${appointmentClasses}`}>
          {appointmentData}
          {appointmentSelection}
        </div>
      </div>
    );
  }
}
