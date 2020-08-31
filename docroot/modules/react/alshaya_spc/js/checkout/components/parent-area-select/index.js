import React from 'react';

import FilterList from '../../../utilities/filter-list';
import {
  getAreasList,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import getStringMessage from '../../../utilities/strings';
import DeliveryInOnlyCity from '../../../utilities/delivery-in-only-city';

export default class ParentAreaSelect extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    let currentOption = [];
    const { default_val: defaultVal, field } = this.props;
    // If default value is available, process that.
    if (defaultVal.length !== 0
      && defaultVal.length !== 'undefined') {
      currentOption = defaultVal[field.key];
    }
    this.state = {
      areas: {},
      currentOption,
      showFilterList: false,
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;

    // Do nothing if parent area is not visible.
    const parentArea = drupalSettings.address_fields.area_parent;
    if (parentArea !== undefined && parentArea.visible === false) {
      return;
    }

    this.getAreasList();
    const { default_val: defaultVal, field, areasUpdate } = this.props;
    if (defaultVal.length !== 0
      && defaultVal.length !== 'undefined') {
      // Once we get parent areas list, get corresponding child areas.
      // this.handleChange(this.props.defaultVal[this.props.field.key]);
      this.setState({
        currentOption: defaultVal[field.key],
      });

      areasUpdate(defaultVal[field.key], false);
    }

    document.addEventListener('updateParentAreaOnMapSelect', this.updateAreaFromGoogleMap, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    // Trigger event for handling area update from map.
    document.removeEventListener('updateParentAreaOnMapSelect', this.updateAreaFromGoogleMap, false);
  }

  /**
   * Whether filter list component need to shown or not.
   */
  toggleFilterList = () => {
    const { showFilterList } = this.state;
    this.setState({
      showFilterList: !showFilterList,
    });

    if (!showFilterList) {
      // Hide contact info and save button on filter list show.
      document.getElementById('spc-checkout-contact-info').classList.add('visually-hidden');
      document.getElementById('address-form-action').classList.add('visually-hidden');
      document.getElementsByClassName('spc-address-form-sidebar')[0].classList.add('block-overflow');
    } else {
      document.getElementById('spc-checkout-contact-info').classList.remove('visually-hidden');
      document.getElementById('address-form-action').classList.remove('visually-hidden');
      document.getElementsByClassName('spc-address-form-sidebar')[0].classList.remove('block-overflow');
    }
  }

  /**
   * Process the value when get from the select list.
   */
  processSelectedItem = (val) => {
    this.setState({
      currentOption: val,
    });

    this.handleChange(val);
  }

  /**
   * Update area field from value of google map.
   */
  updateAreaFromGoogleMap = (e) => {
    if (!this.isComponentMounted) {
      return;
    }
    const data = e.detail.data();
    const { areasUpdate } = this.props;
    this.setState({
      currentOption: data.id,
    });

    areasUpdate(data.id, data.id);
  }

  /**
   * Get the areas list.
   */
  getAreasList = () => {
    this.setState({
      areas: getAreasList(true, null),
    });
  }

  // Handle change of 'area_parent' list.
  handleChange = (selectedOption) => {
    const { areasUpdate } = this.props;
    this.setState({
      currentOption: selectedOption,
    });

    areasUpdate(selectedOption, selectedOption);
  };

  render() {
    const { areas: options, currentOption, showFilterList } = this.state;
    const { field, field_key: fieldKey } = this.props;
    const panelTitle = getStringMessage('address_select', { '@label': field.label });

    let areaLabel = '';
    let hiddenFieldValue = '';
    let showCityOnly = '';

    if (drupalSettings.alshaya_spc.delivery_in_only_city_key) {
      showCityOnly = 'parent-area-only-city';
      if (currentOption === undefined
        || currentOption === null
        || currentOption.toString().length < 1) {
        this.processSelectedItem(drupalSettings.alshaya_spc.delivery_in_only_city_key);
      }
    }

    const currentOptionAvailable = (currentOption !== undefined
      && currentOption !== null
      && currentOption.toString().length > 0);

    if (currentOptionAvailable) {
      hiddenFieldValue = currentOption;
      areaLabel = gerAreaLabelById(true, currentOption).trim();
    }

    const parentArea = drupalSettings.address_fields.area_parent;
    if (parentArea !== undefined && parentArea.visible === false) {
      return (
        <input type="hidden" id={fieldKey} name={fieldKey} value={hiddenFieldValue} />
      );
    }

    return (
      <div className={`spc-type-select ${showCityOnly}`}>
        <label>{field.label}</label>
        {areaLabel.length > 0 ? (
          <div id="spc-area-select-selected-city" className={`spc-area-select-selected ${showCityOnly}`} onClick={() => this.toggleFilterList()}>
            {areaLabel}
          </div>
        ) : (
          <div id="spc-area-select-selected-city" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
            {panelTitle}
          </div>
        )}
        {showFilterList
            && (
            <FilterList
              selected={currentOption}
              options={options}
              placeHolderText={getStringMessage('address_search_for', { '@label': field.label })}
              processingCallback={this.processSelectedItem}
              toggleFilterList={this.toggleFilterList}
              panelTitle={panelTitle}
            />
            )}
        {drupalSettings.alshaya_spc.delivery_in_only_city_key > 0 && (
          <DeliveryInOnlyCity />
        )}

        <input type="hidden" id={fieldKey} name={fieldKey} value={hiddenFieldValue} />
        <div id={`${fieldKey}-error`} />
      </div>
    );
  }
}
