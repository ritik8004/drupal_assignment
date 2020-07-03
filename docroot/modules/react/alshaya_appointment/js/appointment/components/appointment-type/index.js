import React from 'react';
import AppointmentCategories from './components/appointment-categories';
import AppointmentTypeList from './components/appointment-type-list';
import AppointmentCompanion from './components/appointment-companion';
import AppointmentForYou from './components/appointment-for-you';
import AppointmentTermsConditions from './components/appointment-terms-conditions';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import { getInputValue } from '../../../utilities/helper';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../utilities/appointment-util';

const listItems = drupalSettings.alshaya_appointment.appointment_companion_limit;

const companionItems = [...Array(listItems)].map((e, i) => ({ value: i + 1, label: i + 1 }));

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
        appointmentTypeItems: [],
        categoryItems: '',
        activeKey: 0,
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

    // Show loader.
    showFullScreenLoader();

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState({
            appointmentTypeItems: [...result.data],
            appointmentCategory: category,
          });

          // Remove loader.
          removeFullScreenLoader();
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

  onSelectChange = (e, name) => {
    const { value, label } = e;
    this.setState({
      [name]: { id: value, name: label },
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
      activeKey,
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
              onSelectChange={this.onSelectChange}
              activeItem={appointmentType}
              activeKey={activeKey}
            />
          )
          : null}
        { appointmentCategory && appointmentType
          ? (
            <AppointmentCompanion
              appointmentCompanionItems={appointmentCompanionItems}
              onSelectChange={this.onSelectChange}
              activeItem={appointmentCompanion}
              activeKey={activeKey}
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
          className="appointment-type-button fadeInUp"
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
