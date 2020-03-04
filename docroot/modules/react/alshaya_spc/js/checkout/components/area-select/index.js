import React from 'react';

import FilterList from '../../../utilities/filter-list';
import {
  getAreasList,
  gerAreaLabelById
} from '../../../utilities/address_util';
import {
  geocodeAddressToLatLng
} from '../../../utilities/map/map_utils';

export default class AreaSelect extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'areas': [],
      'current_option': this.areaCurrentOption(),
      'showFilterList': false,
      'currentCity': props.cityChanged
    };
  }

  areaCurrentOption = () => {
    let current_option = new Array();
    if (this.props.default_val.length !== 0 &&
      this.props.default_val.length !== 'undefined') {
      current_option = this.props.default_val[this.props.field.key];
    }

    return current_option;
  }

  static getDerivedStateFromProps(props, state) {
    if (props.cityChanged !== state.currentCity) {
      return {'current_option': new Array(), 'currentCity': props.cityChanged}
    }

    return null;
  }

  componentDidMount() {
    this.getAreaList();
    // Only trigger event when area parent field not available.
    if (window.drupalSettings.address_fields.area_parent === undefined) {
      document.addEventListener('updateAreaOnMapSelect', this.updateAreaFromGoogleMap, false);
    }
  }

  /**
   * When we search in google, update address.
   */
  updateAreaFromGoogleMap = (e) => {
    let data = e.detail.data();
    this.setState({
      current_option: data.id,
    });
  }

  /**
   * Whether filter list component need to shown or not.
   */
  toggleFilterList = () => {
    this.setState({
      showFilterList: !this.state.showFilterList
    });

    if (!this.state.showFilterList) {
      // Hide contact info and save button on filter list show.
      document.getElementById('spc-checkout-contact-info').classList.add('visually-hidden');
      document.getElementById('address-form-action').classList.add('visually-hidden');
    }
    else {
      document.getElementById('spc-checkout-contact-info').classList.remove('visually-hidden');
      document.getElementById('address-form-action').classList.remove('visually-hidden');
    }
  };

  /**
   * Process the value when get from the select list.
   */
  processSelectedItem = (val) => {
    this.setState({
      current_option: val.toString(),
    });

    // Geocoding so that map is updated.
    // Calling in timeout to avaoid race condition as
    // component is refreshing and thus elemtent not available.
    setTimeout(function(){
      geocodeAddressToLatLng();
    }, 200);
  };

  // Get area list.
  getAreaList = () => {
    // If no area parent to select.
    if (window.drupalSettings.address_fields.area_parent === undefined) {
      this.setState({
        areas: getAreasList(false, null)
      });
    }
  };

  render() {
    let options = this.state.areas;
    if (this.props.area_list !== null) {
      options = this.props.area_list;
    }

    let panelTitle = Drupal.t('select ') + this.props.field.label;
    let currentOption = this.state.current_option;

    let currentOptionAvailable = (currentOption !== undefined &&
      currentOption !== null &&
      currentOption.toString().length > 0);

    let hiddenFieldValue = '';
    let areaLabel = '';
    if (currentOptionAvailable) {
      hiddenFieldValue = currentOption;
      areaLabel = gerAreaLabelById(false, currentOption).trim();
    }

    return (
      <div className='spc-type-select'>
        <label>{this.props.field.label}</label>
        {
          (areaLabel.length > 0) ? (
          <div id='spc-area-select-selected' className='spc-area-select-selected' onClick={() => this.toggleFilterList()}>
            {areaLabel}
          </div>
        ) : (
          <div id='spc-area-select-selected' className='spc-area-select-selected' onClick={() => this.toggleFilterList()}>
            {Drupal.t('Select area')}
          </div>
        )}
        {this.state.showFilterList &&
          <FilterList
            selected={options[currentOption]}
            options={options}
            placeHolderText={Drupal.t('search for an area')}
            processingCallback={this.processSelectedItem}
            toggleFilterList={this.toggleFilterList}
            panelTitle={panelTitle}
          />
        }
        <input type='hidden' id={this.props.field_key} name={this.props.field_key} value={hiddenFieldValue}/>
        <div id={this.props.field_key + '-error'}/>
      </div>
    );
  }

}
