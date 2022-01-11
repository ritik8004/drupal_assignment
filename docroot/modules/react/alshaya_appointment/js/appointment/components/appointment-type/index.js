import React from 'react';
import 'core-js/es/array';
import AppointmentCategories from './components/appointment-categories';
import AppointmentTypeList from './components/appointment-type-list';
import AppointmentCompanion from './components/appointment-companion';
import AppointmentForYou from './components/appointment-for-you';
import { fetchAPIData } from '../../../utilities/api/fetchApiData';
import { getInputValue, getParam } from '../../../utilities/helper';
import {
  showFullScreenLoader,
  removeFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { smoothScrollTo } from '../../../../../js/utilities/smoothScroll';
import getStringMessage from '../../../../../js/utilities/strings';
import stickyCTAButtonObserver from '../../../utilities/StickyCTA';

const listItems = drupalSettings.alshaya_appointment.appointment_companion_limit;
const companionItems = [...Array(parseInt(listItems, 10))]
  .map((e, i) => ({ value: i + 1, label: i + 1 }));

export default class AppointmentType extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    } else {
      this.state = {
        appointmentStep: 'appointment-type',
        appointmentCategory: '',
        appointmentType: '',
        appointmentCompanion: { value: 1, label: 1 },
        appointmentForYou: 'Yes',
        appointmentTypeItems: [],
        categoryItems: '',
        appointmentCompanionItems: companionItems,
      };
    }
  }

  componentDidMount() {
    // Get Program from Url parameter.
    const program = getParam('program');

    const apiUrl = '/get/programs';
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState({
            categoryItems: result.data,
          }, () => {
            if (program) {
              const categoryIds = result.data.map((value) => value.id);
              if (categoryIds.includes(program)) {
                let category = {};
                for (let i = 0; i < result.data.length; i++) {
                  if (result.data[i].id === program) {
                    category = {
                      id: result.data[i].id,
                      name: result.data[i].name,
                    };
                  }
                }
                this.handleCategoryClick(category);
              }
            }
          });
        }
      });
    }

    // We need a sticky button in mobile.
    if (window.innerWidth < 768) {
      stickyCTAButtonObserver();
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
          // Get program and activity from Url parameter.
          const activity = getParam('activity');
          const program = getParam('program');
          let appointmentTypeParam = '';
          if (program && activity) {
            for (let i = 0; i < result.data.length; i++) {
              if (result.data[i].id === activity) {
                appointmentTypeParam = {
                  label: result.data[i].name,
                  value: result.data[i].id,
                };
              }
            }
          }

          this.setState({
            appointmentTypeItems: [...result.data],
            appointmentCategory: category,
            appointmentType: appointmentTypeParam,
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
    if (name === 'appointmentType') {
      setTimeout(() => {
        smoothScrollTo('.appointment-type-list-wrapper');
      }, 100);
    }
    this.setState({
      [name]: { value, label },
    });
  }

  handleSubmit = () => {
    Drupal.addItemInLocalStorage(
      'appointment_data',
      this.state,
      drupalSettings.alshaya_appointment.local_storage_expire * 60,
    );
    const { handleSubmit } = this.props;
    handleSubmit();
    smoothScrollTo('#appointment-booking');
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
              onSelectChange={this.onSelectChange}
              activeItem={appointmentType}
            />
          )
          : null}
        { appointmentCategory && appointmentType
          ? (
            <AppointmentCompanion
              appointmentCompanionItems={appointmentCompanionItems}
              onSelectChange={this.onSelectChange}
              activeItem={appointmentCompanion}
            />
          )
          : null}
        { appointmentCategory && appointmentType && appointmentCompanion
          ? (
            <AppointmentForYou
              handleChange={this.handleChange}
              activeItem={appointmentForYou}
              appointmentCompanion={appointmentCompanion}
            />
          )
          : null}
        <div className="appointment-flow-action">
          <button
            className="appointment-type-button fadeInUp"
            type="button"
            disabled={!(appointmentCategory
              && appointmentType
              && appointmentCompanion
              && appointmentForYou)}
            onClick={this.handleSubmit}
          >
            {getStringMessage('continue')}
          </button>
        </div>
        <div id="appointment-bottom-sticky-edge" />
      </div>
    );
  }
}
