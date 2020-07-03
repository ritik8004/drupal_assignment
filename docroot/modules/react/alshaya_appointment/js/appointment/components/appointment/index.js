import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import AppointmentStoreSelect from '../appointment-store';
import AppointmentSelection from '../appointment-selection';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import AppointmentTimeSlot from "../appointment-timeslot";
import AppointmentLogin from "../appointment-login";

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

    if (appointmentStep === 'appointment-type') {
      appointmentData = (
        <AppointmentType
          handleSubmit={() => this.handleSubmit('select-store')}
        />
      );
    } else if (appointmentStep === 'select-store') {
      appointmentData = (
        <AppointmentStoreSelect
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('select-time-slot')}
        />
      );
    } else if (appointmentStep === 'select-time-slot') {
      appointmentData = (
        <AppointmentTimeSlot
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('customer-details')}
        />
      );
    } else if (appointmentStep === 'select-login-guest') {
      appointmentData = (
        <AppointmentLogin
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('customer')}
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
        <div className="appointment-inner-wrapper">
          {appointmentData}
          {appointmentSelection}
        </div>
      </div>
    );
  }
}
