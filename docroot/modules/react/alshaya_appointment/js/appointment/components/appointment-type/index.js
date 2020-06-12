import React from 'react';
import AppointmentCategories from './components/appointment-categories';
import AppointmentTypeList from './components/appointment-type-list';
import AppointmentCompanion from './components/appointment-companion';
import AppointmentForYou from './components/appointment-for-you';
import AppointmentAck from './components/appointment-ack';

export default class AppointmentType extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = JSON.parse(localStorage.getItem('appointment_data'));
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    } else {
      this.state = {
        appointmentCategory: '',
        appointmentType: '',
        appointmentCompanion: '',
        appointmentForYou: '',
        appointmentAck: '',
      };
    }
  }

  handleCategoryClick = (category) => {
    this.setState({
      appointmentCategory: category,
    });
  }

  handleChange = (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
    this.setState({
      [e.target.name]: value,
    });
  }

  handleSubmit = () => {
    localStorage.setItem('appointment_data', JSON.stringify(this.state));
  }

  render() {
    const {
      appointmentCategory, appointmentType, appointmentCompanion, appointmentForYou, appointmentAck,
    } = this.state;
    return (
      <div className="appointment-type-wrapper">
        <AppointmentCategories
          handleItemClick={this.handleCategoryClick}
          activeItem={appointmentCategory}
        />
        { appointmentCategory
          ? (
            <AppointmentTypeList
              handleChange={this.handleChange}
              activeItem={appointmentType}
            />
          )
          : null}
        { appointmentCategory && appointmentType
          ? (
            <AppointmentCompanion
              handleChange={this.handleChange}
              activeItem={appointmentCompanion}
            />
          )
          : null}
        { appointmentCategory && appointmentType && appointmentCompanion
          ? (
            <AppointmentForYou
              handleChange={this.handleChange}
              activeItem={appointmentForYou}
            />
          )
          : null}
        { appointmentCategory && appointmentType && appointmentCompanion && appointmentForYou
          ? (
            <AppointmentAck
              handleChange={this.handleChange}
              activeItem={appointmentAck}
            />
          )
          : null}
        <button
          className="appointment-type-button"
          type="button"
          disabled={!(appointmentCategory
            && appointmentType
            && appointmentCompanion
            && appointmentForYou
            && appointmentAck)}
          onClick={this.handleSubmit}
        >
          {Drupal.t('Continue')}
        </button>
      </div>
    );
  }
}
