import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import AppointmentStore from '../appointment-store';
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
    localStorageValues.appointmentStep = 2;
    setStorageInfo(localStorageValues);

    this.setState({
      appointmentStep: 2,
    });
  }

  render() {
    const {
      appointmentStep,
    } = this.state;

    return (
      <div className="appointment-wrapper">
        <AppointmentSteps />
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
              handleSubmit={this.handleSubmit}
            />
          )
          : null}
      </div>
    );
  }
}
