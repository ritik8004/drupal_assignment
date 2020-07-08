import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import AppointmentStoreSelect from '../appointment-store';
import AppointmentSelection from '../appointment-selection';
import CustomerDetails from '../customer-details';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import AppointmentTimeSlot from '../appointment-timeslot';
import AppointmentLogin from '../appointment-login';

export default class Appointment extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
      const userId = drupalSettings.userDetails.userID;
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
    const userId = drupalSettings.userDetails.userID;
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
        <AppointmentStoreSelect
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('select-time-slot')}
        />
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
    }

    if (appointmentStep !== 'appointment-type') {
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
