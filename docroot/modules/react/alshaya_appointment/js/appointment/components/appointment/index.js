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
        appointmentStep: 1,
      };
    }
  }

  handleSubmit = () => {
    const localStorageValues = getStorageInfo();
    const {
      appointmentStep,
    } = this.state;

    localStorageValues.appointmentStep = appointmentStep + 1;
    setStorageInfo(localStorageValues);

    this.setState({
      appointmentStep: appointmentStep + 1,
    });
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

    return (
      <div className="appointment-wrapper">
        <AppointmentSteps />

        <div className="appointment-inner-wrapper">
          { (appointmentStep === 1)
            ? (
              <AppointmentType
                handleSubmit={this.handleSubmit}
              />
            )
            : null}
          { (appointmentStep === 2)
            ? (
              <AppointmentStore
                handleBack={this.handleEdit}
                handleSubmit={this.handleSubmit}
              />
            )
            : null}

          { (appointmentStep > 1)
            ? (
              <AppointmentSelection
                handleEdit={this.handleEdit}
              />
            )
            : null}
        </div>
      </div>
    );
  }
}
