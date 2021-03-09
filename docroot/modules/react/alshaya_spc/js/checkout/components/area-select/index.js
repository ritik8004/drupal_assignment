import React from 'react';

import FilterList from '../../../utilities/filter-list';
import {
  getAreasList,
  gerAreaLabelById, getAreaParentId,
} from '../../../utilities/address_util';
import getStringMessage from '../../../utilities/strings';
import ConditionalView from '../../../common/components/conditional-view';

export default class AreaSelect extends React.Component {
  isComponentMounted = true;

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
      return { currentOption: null, currentCity: props.cityChanged };
    }

    return null;
  }

  componentDidMount() {
    this.isComponentMounted = true;
    this.getAreaList();
    this.populateParentIfHidden(document.getElementById('administrative_area').value);

    // Trigger event for handling area update from map.
    document.addEventListener('updateAreaOnMapSelect', this.updateAreaFromGoogleMap);
  }

  componentDidUpdate() {
    this.preSelectDefaultIfPossible();
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    // Trigger event for handling area update from map.
    document.removeEventListener('updateAreaOnMapSelect', this.updateAreaFromGoogleMap);
  }

  preSelectDefaultIfPossible = () => {
    const { area_list: areaList } = this.props;
    // Do nothing if area list is empty.
    if (areaList === null) {
      return;
    }

    const { currentOption } = this.state;
    if (currentOption === null && Object.values(areaList).length === 1) {
      this.setState({
        currentOption: Object.values(areaList)[0].value,
      });
    }
  };

  areaCurrentOption = () => {
    let currentOption = null;

    const { default_val: defaultVal, field } = this.props;
    if (defaultVal.length !== 0) {
      currentOption = defaultVal[field.key];
    }

    return currentOption;
  };

  /**
   * When we search in google, update address.
   */
  updateAreaFromGoogleMap = (e) => {
    if (!this.isComponentMounted) {
      return;
    }
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
      document.getElementsByClassName('spc-address-form-sidebar')[0].classList.add('block-overflow');
    } else {
      document.getElementById('spc-checkout-contact-info').classList.remove('visually-hidden');
      document.getElementById('address-form-action').classList.remove('visually-hidden');
      document.getElementsByClassName('spc-address-form-sidebar')[0].classList.remove('block-overflow');
    }
  };

  populateParentIfHidden = (val) => {
    // Do nothing if we have no address available.
    if (val === undefined || !(val)) {
      return;
    }

    const parentArea = drupalSettings.address_fields.area_parent;
    if (parentArea !== undefined || parentArea.visible === false) {
      const areaParentInput = document.getElementById('area_parent');
      if (areaParentInput) {
        const areaParentInputValue = getAreaParentId(false, val.toString());
        areaParentInput.value = areaParentInputValue ? areaParentInputValue[0].id : null;
      }
    }
  };

  /**
   * Process the value when get from the select list.
   */
  processSelectedItem = (val) => {
    this.setState({
      currentOption: val.toString(),
    });

    this.populateParentIfHidden(val);
  };

  // Get area list.
  getAreaList = () => {
    const parentArea = drupalSettings.address_fields.area_parent;

    // If no area parent to select.
    if (parentArea === undefined || parentArea.visible === false) {
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

    const panelTitle = getStringMessage('address_select', { '@label': field.label });

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
      <div className={`spc-type-select area-options-count-${options.length}`}>
        <label>{field.label}</label>

        <div id="spc-area-select-selected" className="spc-area-select-selected" onClick={() => this.toggleFilterList()}>
          { (areaLabel.length > 0) ? areaLabel : panelTitle }
        </div>

        <ConditionalView condition={showFilterList}>
          <FilterList
            selected={currentOption}
            options={options}
            placeHolderText={getStringMessage('address_search_for', { '@label': field.label })}
            processingCallback={this.processSelectedItem}
            toggleFilterList={this.toggleFilterList}
            panelTitle={panelTitle}
          />
        </ConditionalView>

        <input type="hidden" id={fieldKey} name={fieldKey} value={hiddenFieldValue} />

        <div id={`${fieldKey}-error`} />
      </div>
    );
  }
}
