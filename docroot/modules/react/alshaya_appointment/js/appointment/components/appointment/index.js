import React from 'react';
import AppointmentSteps from "../appointment-steps";
import AppointmentType from "../appointment-type";

const Appointment = () => (
  <div className="appointment-wrapper">
    <AppointmentSteps />
    <AppointmentType />
    <span>Placeholder for appointment form</span>
  </div>
);

export default Appointment;
