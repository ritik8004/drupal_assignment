import React from 'react';
import AppointmentCategories from "../appointment-type/components/appointment-categories";
import AppointmentTypeList from "../appointment-type/components/appointment-type-list";
import AppointmentCompanion from "../appointment-type/components/appointment-companion";
import AppointmentForYou from "../appointment-type/components/appointment-for-you";
import AppointmentAck from "../appointment-type/components/appointment-ack";

export default class AppointmentType extends React.Component {
  constructor(props) {
    super(props);
    var localStorageValues = JSON.parse(localStorage.getItem('appointment_data'));
    if (localStorageValues) {
      this.state = {
        ...localStorageValues
      };
    } else {
      this.state = {
        appointmentCategory: '',
        appointmentType: '',
        appointmentCompanion: '',
        appointmentForYou: 'Yes',
        appointmentAck: '',
      };
    }
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

  handleSubmit = () => {
    localStorage.setItem('appointment_data', JSON.stringify(this.state));
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
          activeItem = {this.state.appointmentType}
        />
        <AppointmentCompanion
          handleChange = {this.handleChange}
          activeItem = {this.state.appointmentCompanion}
        />
        <AppointmentForYou
          handleChange = {this.handleChange}
          activeItem = {this.state.appointmentForYou}
        />
        <AppointmentAck
          handleChange = {this.handleChange}
          activeItem = {this.state.appointmentAck}
        />

        <button
          className="appointment-type-button"
          type="button"
          onClick={this.handleSubmit}
        >
          {Drupal.t('Continue')}
        </button>
      </div>
    );
  }
}

