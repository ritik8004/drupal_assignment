import React from 'react';
import AppointmentSteps from '../appointment-steps';
import AppointmentType from '../appointment-type';

const Appointment = () => (
  <div className="appointment-wrapper">
    <AppointmentSteps />
    <AppointmentType />
  </div>
);

export default Appointment;
