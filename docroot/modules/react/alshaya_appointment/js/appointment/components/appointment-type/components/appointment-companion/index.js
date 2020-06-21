import React from 'react';

const AppointmentCompanionItems = [
  { value: '', label: 'Please Select' },
  { value: '1', label: '1' },
];

export default class AppointmentCompanion extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
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
          {AppointmentCompanionItems.map((v) => (
            <option
              value={v.value}
              selected={activeItem === v.value}
            >
              {v.label}
            </option>
          ))}
        </select>
      </div>
    );
  }
}
