import React from 'react';

const ListItems = () => {
  const listItems = drupalSettings.step_labels;

  return listItems.map((item) => (
    <li
      className={item.step === 1 ? 'active wizard-step' : 'wizard-step'}
      value={item.step}
    >
      <span className="step-number">{item.step}</span>
      <span className="step-title">{item.stepTitle}</span>
    </li>
  ));
};

const AppointmentSteps = () => (
  <div className="appointment-steps-wrap">
    <ul className="appointment-steps">
      <ListItems />
    </ul>
  </div>
);

export default AppointmentSteps;
