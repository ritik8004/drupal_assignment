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

    // Geocoding so that map is updated.
    // Calling in timeout to avaoid race condition as
    // component is refreshing and thus elemtent not available.
    setTimeout(() => {
      geocodeAddressToLatLng();
    }, 200);
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
    const panelTitle = Drupal.t('Select @label', { '@label': field.label });

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
        <label>{field.label}</label>
        {areaLabel.length > 0 ? (
          <div id="spc-area-select-selected-city" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
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
              placeHolderText={Drupal.t('Search for @label', { '@label': field.label })}
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
