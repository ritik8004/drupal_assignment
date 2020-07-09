import React from 'react';

export default class AppointmentsView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      appointments: 'Placeholder',
    };
  }

  render() {
    const { appointments } = this.state;
    return (
      <div className="appointments-list-wrapper">
        <p>
          { appointments }
        </p>
      </div>
    );
  }
}
