import React from 'react';

import FilterList from '../../../utilities/filter-list';
import {
  getAreasList,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import {
  geocodeAddressToLatLng,
} from '../../../utilities/map/map_utils';

export default class AreaSelect extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      areas: [],
      currentOption: this.areaCurrentOption(),
      showFilterList: false,
      currentCity: props.cityChanged,
    };
  }

  static getDerivedStateFromProps(props, state) {
    if (props.cityChanged !== state.currentCity) {
      return { currentOption: [], currentCity: props.cityChanged };
    }

    return null;
  }

  componentDidMount() {
    this.getAreaList();
    // Trigger event for handling area update from map.
    document.addEventListener('updateAreaOnMapSelect', this.updateAreaFromGoogleMap, false);
  }

  areaCurrentOption = () => {
    let currentOption = [];
    const { default_val: defaultVal, field } = this.props;
    if (defaultVal.length !== 0
      && defaultVal.length !== 'undefined') {
      currentOption = defaultVal[field.key];
    }

    return currentOption;
  }

  /**
   * When we search in google, update address.
   */
  updateAreaFromGoogleMap = (e) => {
    const data = e.detail.data();
    this.setState({
      currentOption: data.id,
    });
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
    } else {
      document.getElementById('spc-checkout-contact-info').classList.remove('visually-hidden');
      document.getElementById('address-form-action').classList.remove('visually-hidden');
    }
  };

  /**
   * Process the value when get from the select list.
   */
  processSelectedItem = (val) => {
    this.setState({
      currentOption: val.toString(),
    });

    // Geocoding so that map is updated.
    // Calling in timeout to avaoid race condition as
    // component is refreshing and thus elemtent not available.
    setTimeout(() => {
      geocodeAddressToLatLng();
    }, 200);
  };

  // Get area list.
  getAreaList = () => {
    // If no area parent to select.
    if (window.drupalSettings.address_fields.area_parent === undefined) {
      this.setState({
        areas: getAreasList(false, null),
      });
    }
  };

  render() {
    const {
      areas,
      currentOption,
      showFilterList,
    } = this.state;
    const {
      area_list: areaList,
      field,
      field_key: fieldKey,
    } = this.props;
    let options = areas;
    if (areaList !== null) {
      options = areaList;
    }

    const panelTitle = Drupal.t('select @label', { '@label': field.label });

    const currentOptionAvailable = (currentOption !== undefined
      && currentOption !== null
      && currentOption.toString().length > 0);

    let hiddenFieldValue = '';
    let areaLabel = '';
    if (currentOptionAvailable) {
      hiddenFieldValue = currentOption;
      areaLabel = gerAreaLabelById(false, currentOption).trim();
    }

    return (
      <div className="spc-type-select">
        <label>{field.label}</label>
        {
          (areaLabel.length > 0) ? (
            <div id="spc-area-select-selected" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
              {areaLabel}
            </div>
          ) : (
            <div id="spc-area-select-selected" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
              {Drupal.t('Select area')}
            </div>
          )
}
        {showFilterList
          && (
          <FilterList
            selected={options[currentOption]}
            options={options}
            placeHolderText={Drupal.t('search for an area')}
            processingCallback={this.processSelectedItem}
            toggleFilterList={this.toggleFilterList}
            panelTitle={panelTitle}
          />
          )}
        <input type="hidden" id={fieldKey} name={fieldKey} value={hiddenFieldValue} />
        <div id={`${fieldKey}-error`} />
      </div>
    );
  }
}
