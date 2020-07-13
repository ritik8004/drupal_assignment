import React from 'react';

export default class AppointmentlistType extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const { appointment } = this.props;

    let activityName = '';
    if (appointment !== undefined) {
      activityName = appointment.activityName;
    }

    return (
      <div className="appointment-list-type">
        <span>
          { Drupal.t('Appointment type') }
        </span>
        <span>
          { activityName }
        </span>
      </div>
    );
  }
}
