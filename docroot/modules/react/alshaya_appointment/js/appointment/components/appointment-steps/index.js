import React from 'react';
import 'core-js/es/array';
import AppointmentMessages from '../appointment-messages';

const AppointmentSteps = (props) => {
  const { step } = props;
  const { uid } = drupalSettings.user;
  const listItems = drupalSettings.alshaya_appointment.step_labels;
  if (uid) {
    if (listItems[3].stepValue === 'select-login-guest') {
      listItems.splice(3, 1);
    }
  }

  const getStepClass = (itemStep, activeStep) => {
    let stepClass = 'wizard-step ';
    if (itemStep === activeStep) {
      stepClass += `active ${itemStep}`;
    } else {
      stepClass += ` ${itemStep}`;
    }
    return stepClass;
  };

  const getSteps = (steps) => {
    const steprender = steps.map((item, index) => (
      <li
        key={item.step}
        className={getStepClass(item.stepValue, step)}
        value={item.stepValue}
      >
        <span className="step-number"><span>{index + 1}</span></span>
        <span className="step-title">{item.stepTitle}</span>
      </li>
    ));

    return steprender;
  };

  return (
    <div className="appointment-steps-wrap fadeInUp">
      <ul className="appointment-steps">
        { getSteps(listItems) }
      </ul>
      <AppointmentMessages />
    </div>
  );
};

export default AppointmentSteps;
