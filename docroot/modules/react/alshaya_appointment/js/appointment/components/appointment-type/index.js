import React from 'react';
import AppointmentCategories from './components/appointment-categories';
import AppointmentTypeList from './components/appointment-type-list';
import AppointmentCompanion from './components/appointment-companion';
import AppointmentForYou from './components/appointment-for-you';
import AppointmentTermsConditions from './components/appointment-terms-conditions';
import fetchAPIData from '../../../utilities/api/fetchApiData';

const defaultSelectOption = Drupal.t('Please Select');

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
        appointmentTermsConditions: '',
        appointmentTypeItems: [{ id: '', name: defaultSelectOption }],
        categoryItems: '',
        appointmentCompanionItems: [{ value: '', label: defaultSelectOption }, { value: '1', label: '1' }],
      };
    }
  }

  componentDidMount() {
    this.fetchPrograms();
  }

  handleCategoryClick = (category) => {
    this.fetchActivities(category.id);
    this.setState({
      appointmentCategory: category.name,
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

  fetchPrograms = () => {
    const apiUrl = '/get/programs';
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState({
            categoryItems: result.data,
          });
        }
      });
    }
  }

  fetchActivities = (categoryId) => {
    const apiUrl = `/get/activities?program=${categoryId}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState({
            appointmentTypeItems: [{ id: '', name: defaultSelectOption }, ...result.data],
          });
        }
      });
    }
  }

  render() {
    const {
      categoryItems,
      appointmentTypeItems,
      appointmentCompanionItems,
      appointmentCategory,
      appointmentType,
      appointmentCompanion,
      appointmentForYou,
      appointmentTermsConditions,
    } = this.state;

    return (
      <div className="appointment-type-wrapper">
        <AppointmentCategories
          categoryItems={categoryItems}
          handleItemClick={this.handleCategoryClick}
          activeItem={appointmentCategory}
        />
        { appointmentCategory
          ? (
            <AppointmentTypeList
              appointmentTypeItems={appointmentTypeItems}
              handleChange={this.handleChange}
              activeItem={appointmentType}
            />
          )
          : null}
        { appointmentCategory && appointmentType
          ? (
            <AppointmentCompanion
              appointmentCompanionItems={appointmentCompanionItems}
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
            <AppointmentTermsConditions
              handleChange={this.handleChange}
              activeItem={appointmentTermsConditions}
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
            && appointmentTermsConditions)}
          onClick={this.handleSubmit}
        >
          {Drupal.t('Continue')}
        </button>
      </div>
    );
  }
}
