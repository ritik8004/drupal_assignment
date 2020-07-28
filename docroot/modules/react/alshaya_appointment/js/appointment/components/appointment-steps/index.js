import React from 'react';
import 'core-js/es/array';

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
        stepClass = 'active wizard-step';
      } else if (visitedStep.includes(itemStep)) {
        stepClass = 'visited wizard-step';
      } else {
        stepClass = 'wizard-step';
      }
      visitedStep.push(activeStep);
      return stepClass;
    };

    const { step } = this.props;
    const listItems = drupalSettings.alshaya_appointment.step_labels;
    const steprender = listItems.map((item) => (
      <li
        key={item.step}
        className={getStepClass(item.stepValue, step)}
        value={item.stepValue}
      >
        <span className="step-number">{item.step}</span>
        <span className="step-title">{item.stepTitle}</span>
      </li>
    ));

    return (
      <div className="appointment-steps-wrap fadeInUp">
        <ul className="appointment-steps">
          { steprender }
        </ul>
      </div>
    );
  }
}
