import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import AppointmentStore from '../appointment-store';
import AppointmentSelection from '../appointment-selection';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';

export default class Appointment extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    } else {
      this.state = {
        appointmentStep: 'appointment-type',
      };
    }
  }

  handleSubmit = (stepValue) => {
    const localStorageValues = getStorageInfo();

    localStorageValues.appointmentStep = stepValue;
    setStorageInfo(localStorageValues);

    this.setState((prevState) => ({
      ...prevState,
      appointmentStep: stepValue,
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
        <AppointmentStore
          handleBack={this.handleEdit}
          handleSubmit={() => this.handleSubmit('select-time-slot')}
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
