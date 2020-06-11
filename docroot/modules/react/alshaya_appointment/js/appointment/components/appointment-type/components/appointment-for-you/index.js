import React from 'react';

export default class AppointmentForYou extends React.Component {
  constructor(props) {
    super(props);
  }

  handleChange = (e) => {
    this.props.handleChange(e);
  }

  render () {
    return (
      <div className="appointment-for-you-wrapper">
        <label>{Drupal.t('Is one of these appointments for you?')}*</label>
        <div className="appointment-for-you">
          <label>
            <input
              type="radio"
              value="Yes"
              name="appointmentForYou"
              checked={this.props.appointmentForYou === "Yes"}
              onChange={this.handleChange}
            />
            {Drupal.t('Yes')}
          </label>
          <label>
            <input
              type="radio"
              value="No"
              name="appointmentForYou"
              checked={this.props.appointmentForYou === "No"}
              onChange={this.handleChange}
            />
            {Drupal.t('No')}
          </label>
        </div>
      </div>
    );
  };

} 