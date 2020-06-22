import React from 'react';

export default class AppointmentForYou extends React.Component {
  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  render() {
    const { activeItem } = this.props;
    return (
      <div className="appointment-for-you-wrapper">
        <label>
          {Drupal.t('Is one of these appointments for you?')}
          *
        </label>
        <div className="appointment-for-you">
          <label>
            <input
              type="radio"
              value="Yes"
              name="appointmentForYou"
              checked={activeItem === 'Yes'}
              onChange={this.handleChange}
            />
            {Drupal.t('Yes')}
          </label>
          <label>
            <input
              type="radio"
              value="No"
              name="appointmentForYou"
              checked={activeItem === 'No'}
              onChange={this.handleChange}
            />
            {Drupal.t('No')}
          </label>
        </div>
      </div>
    );
  }
}
