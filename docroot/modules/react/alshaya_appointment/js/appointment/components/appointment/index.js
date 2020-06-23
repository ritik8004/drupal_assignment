import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';
import AppointmentStore from '../appointment-store'

const Appointment = () => (
  <div className="appointment-wrapper">
    <AppointmentSteps />
    <AppointmentType />
    <AppointmentStore />
  </div>
);

export default Appointment;
