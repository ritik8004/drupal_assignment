import React from 'react';

const AppointmentAckText = "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.";

export default class AppointmentAck extends React.Component {
  constructor(props) {
    super(props);
  }

  handleChange = (e) => {
    this.props.handleChange(e);
  }

  render () {
    return (
      <div className="appointment-ack-wrapper">
        <input
          type="checkbox"
          name="appointmentAck"
          onChange={this.handleChange}
        />
        <div className="appointment-ack-inner-wrapper">
          <label>{Drupal.t('Please tick to confirm the following')}*</label>
          <div className="">
            {AppointmentAckText}
          </div>
        </div>
      </div>
    );
  };

} 