import React from 'react';

export default class AppointmentSteps extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const { step } = this.props;
    const listItems = drupalSettings.alshaya_appointment.step_labels;
    const steprender = listItems.map((item) => (
      <li
        key={item.step}
        className={item.stepValue === step ? 'active wizard-step' : 'wizard-step'}
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
