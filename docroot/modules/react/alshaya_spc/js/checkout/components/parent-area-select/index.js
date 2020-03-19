import React from 'react';

import FilterList from '../../../utilities/filter-list';
import {
  getAreasList,
  gerAreaLabelById,
} from '../../../utilities/address_util';
import {
  geocodeAddressToLatLng,
} from '../../../utilities/map/map_utils';

export default class ParentAreaSelect extends React.Component {
  constructor(props) {
    super(props);
    let currentOption = [];
    // If default value is available, process that.
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      currentOption = this.props.default_val[this.props.field.key];
    }
    this.state = {
      areas: {},
      currentOption,
      showFilterList: false,
    };
  }

  /**
   * Whether filter list component need to shown or not.
   */
  toggleFilterList = () => {
    this.setState({
      showFilterList: !this.state.showFilterList,
    });

    if (!this.state.showFilterList) {
      // Hide contact info and save button on filter list show.
      document.getElementById('spc-checkout-contact-info').classList.add('visually-hidden');
      document.getElementById('address-form-action').classList.add('visually-hidden');
    } else {
      document.getElementById('spc-checkout-contact-info').classList.remove('visually-hidden');
      document.getElementById('address-form-action').classList.remove('visually-hidden');
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

    // Geocoding so that map is updated.
    // Calling in timeout to avaoid race condition as
    // component is refreshing and thus elemtent not available.
    setTimeout(() => {
      geocodeAddressToLatLng();
    }, 200);
  }

  componentDidMount() {
    this.getAreasList();
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      // Once we get parent areas list, get corresponding child areas.
      // this.handleChange(this.props.default_val[this.props.field.key]);
      this.setState({
        currentOption: this.props.default_val[this.props.field.key],
      });

      this.props.areasUpdate(this.props.default_val[this.props.field.key], false);
    }

    document.addEventListener('updateParentAreaOnMapSelect', this.updateAreaFromGoogleMap, false);
  }

  /**
   * Update area field from value of google map.
   */
  updateAreaFromGoogleMap = (e) => {
    const data = e.detail.data();
    this.setState({
      currentOption: data.id,
    });

    this.props.areasUpdate(data.id, data.id);
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
    this.setState({
      currentOption: selectedOption,
    });

    this.props.areasUpdate(selectedOption, selectedOption);
  };

  render() {
    const options = this.state.areas;
    const panelTitle = Drupal.t('select @label', { '@label': this.props.field.label });
    const { currentOption } = this.state;

    const currentOptionAvailable = (currentOption !== undefined
      && currentOption !== null
      && currentOption.toString().length > 0);

    let areaLabel = '';
    let hiddenFieldValue = '';
    if (currentOptionAvailable) {
      hiddenFieldValue = currentOption;
      areaLabel = gerAreaLabelById(true, currentOption).trim();
    }

    return (
      <div className="spc-type-select">
        <label>{this.props.field.label}</label>
        {areaLabel.length > 0 ? (
          <div id="spc-area-select-selected-city" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
            {areaLabel}
          </div>
        ) : (
          <div id="spc-area-select-selected-city" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
            {Drupal.t('Select city')}
          </div>
        )}
        {this.state.showFilterList
            && (
            <FilterList
              selected={options[currentOption]}
              options={options}
              placeHolderText={Drupal.t('search for a city')}
              processingCallback={this.processSelectedItem}
              toggleFilterList={this.toggleFilterList}
              panelTitle={panelTitle}
            />
            )}
        <input type="hidden" id={this.props.field_key} name={this.props.field_key} value={hiddenFieldValue} />
        <div id={`${this.props.field_key}-error`} />
      </div>
    );
  }
}
