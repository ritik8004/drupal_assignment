import React from 'react';
import 'core-js/es/array';
import AppointmentMessages from '../appointment-messages';

export default class AppointmentSteps extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      visitedStep: [],
    };
  }

  render() {
    const getStepClass = (itemStep, activeStep) => {
      let stepClass = '';
      const { visitedStep } = this.state;
      if (itemStep === activeStep) {
        stepClass = `active wizard-step ${itemStep}`;
      } else if (visitedStep.includes(itemStep)) {
        stepClass = `visited wizard-step ${itemStep}`;
      } else {
        stepClass = `wizard-step ${itemStep}`;
      }
      visitedStep.push(activeStep);
      return stepClass;
    };

    const { step } = this.props;
    const { uid } = drupalSettings.user;
    const listItems = drupalSettings.alshaya_appointment.step_labels;
    if (uid) {
      if (listItems[3].stepValue === 'select-login-guest') {
        listItems.splice(3, 1);
      }
    }
    const steprender = listItems.map((item, index) => (
      <li
        key={item.step}
        className={getStepClass(item.stepValue, step)}
        value={item.stepValue}
      >
        <span className="step-number">{index + 1}</span>
        <span className="step-title">{item.stepTitle}</span>
      </li>
    ));

    return (
      <div className="appointment-steps-wrap fadeInUp">
        <ul className="appointment-steps">
          { steprender }
        </ul>
        <AppointmentMessages />
      </div>
    );
  }
}
