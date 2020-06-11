import React from 'react';
import AppointmentCategories from "../appointment-type/components/appointment-categories";
import AppointmentTypeList from "../appointment-type/components/appointment-type-list";
import AppointmentCompanion from "../appointment-type/components/appointment-companion";
import AppointmentForYou from "../appointment-type/components/appointment-for-you";
import AppointmentAck from "../appointment-type/components/appointment-ack";

export default class AppointmentType extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      appointmentCategory: '',
      appointmentType: '',
      appointmentCompanion: '',
      appointmentForYou: 'Yes',
      appointmentAck: '',
    };
    // this.state = {
    //   appointment_data: JSON.parse(localStorage.getItem('appointment_data')) || []
    // }
    console.log(localStorage.getItem('appointment_data'))
  }

  handleCategoryClick = (category) => {
    this.setState({
      appointmentCategory: category
    });
  }

  handleChange = (e) => {
    var value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
    this.setState({
      [e.target.name]: value
    });
  }

  handleClick = () => {
    localStorage.setItem('appointment_data', JSON.stringify(this.state));
    console.log(localStorage.getItem('appointment_data'))
  }

  render () {
    return (
      <div className="appointment-type-wrapper">
        <AppointmentCategories 
          handleItemClick = {this.handleCategoryClick}
          activeItem = {this.state.appointmentCategory}
        />
        <AppointmentTypeList
          handleChange = {this.handleChange}
        />
        <AppointmentCompanion
          handleChange = {this.handleChange}
        />
        <AppointmentForYou
          handleChange = {this.handleChange}
          appointmentForYou = {this.state.appointmentForYou}
        />
        <AppointmentAck
          handleChange = {this.handleChange}
        />

        <button
          className="appointment-type-button"
          type="button"
          onClick={this.handleClick}
        >
          {Drupal.t('Continue')}
        </button>
      </div>
    );
  }
}

