import React from 'react';
import AppointmentCategories from './components/appointment-categories';
import AppointmentTypeList from './components/appointment-type-list';
import AppointmentCompanion from './components/appointment-companion';
import AppointmentForYou from './components/appointment-for-you';
import AppointmentTermsConditions from './components/appointment-terms-conditions';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import { getInputValue } from '../../../utilities/helper';

const defaultSelectOption = Drupal.t('Please Select');
const listItems = drupalSettings.alshaya_appointment.appointment_companion_limit;

const companionItems = [...Array(listItems + 1)].map((e, i) => {
  const companionNum = (i === 0) ? defaultSelectOption : i;
  return { value: i, label: companionNum };
});

export default class AppointmentType extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    } else {
      this.state = {
        appointmentStep: 'appointment-type',
        appointmentCategory: '',
        appointmentType: '',
        appointmentCompanion: '',
        appointmentForYou: '',
        appointmentTermsConditions: '',
        appointmentTypeItems: [{ id: '', name: defaultSelectOption }],
        categoryItems: '',
        appointmentCompanionItems: companionItems,
      };
    }
  }

  componentDidMount() {
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

  handleCategoryClick = (category) => {
    const apiUrl = `/get/activities?program=${category.id}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState({
            appointmentTypeItems: [{ id: '', name: defaultSelectOption }, ...result.data],
            appointmentCategory: category,
          });
        }
      });
    }
  }

  handleChange = (e) => {
    const value = getInputValue(e);
    this.setState({
      [e.target.name]: value,
    });
  }

  handleSubmit = () => {
    setStorageInfo(this.state);
    const { handleSubmit } = this.props;
    handleSubmit();
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
          activeItem={appointmentCategory.id}
        />
        { appointmentCategory
          ? (
            <AppointmentTypeList
              appointmentTypeItems={appointmentTypeItems}
              handleChange={this.handleChange}
              activeItem={appointmentType.id}
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
