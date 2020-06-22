import React from 'react';

export default class AppointmentCompanion extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { appointmentCompanionItems, activeItem } = this.props;
    return (
      <div className="appointment-companion-wrapper">
        <label>
          {Drupal.t('How many people do you want to book the appointment for?')}
          *
        </label>
        <select
          className="appointment-companion-select"
          name="appointmentCompanion"
          onChange={this.handleChange}
        >
          {appointmentCompanionItems.map((v) => (
            <option
              value={v.value}
              selected={parseInt(activeItem, 10) === v.value}
            >
              {v.label}
            </option>
          ))}
        </select>
      </div>
    );
  }
}
