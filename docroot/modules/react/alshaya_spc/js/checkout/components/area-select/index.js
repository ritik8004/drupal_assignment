import React from 'react';

import FilterList from '../../../utilities/filter-list';
import {
  getAreasList
} from '../../../utilities/address_util';

export default class AreaSelect extends React.Component {

  constructor(props) {
    super(props);
    let current_option = new Array();
    // If default value is available, process that.
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      current_option = this.props.default_val[this.props.field.key];
    }
    this.state = {
      'areas': [],
      'current_option': current_option,
      'showFilterList': false
    };
  }

  componentDidMount() {
    this.getAreaList();

    document.addEventListener('updateAreaOnMapSelect', this.updateAreaFromGoogleMap, false);
  }

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
      current_option: val
    });
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
    let label = options.length > 0
      ? options[this.state.current_option]['label']
      : '';

    return (
      <div className='spc-type-select'>
        <label>{this.props.field.label}</label>
        {
          (this.state.current_option !== undefined
            && this.state.current_option !== null
            && this.state.current_option.length !== 0) ? (
          <div id='spc-area-select-selected' className='spc-area-select-selected' onClick={() => this.toggleFilterList()}>
            {label}
          </div>
        ) : (
          <div id='spc-area-select-selected' className='spc-area-select-selected' onClick={() => this.toggleFilterList()}>
            {Drupal.t('Select area')}
          </div>
        )}
        {this.state.showFilterList &&
          <FilterList
            selected={options[this.state.current_option]}
            options={options}
            placeHolderText={Drupal.t('search for an area')}
            processingCallback={this.processSelectedItem}
            toggleFilterList={this.toggleFilterList}
            panelTitle={panelTitle}
          />
        }
        <input type='hidden' id={this.props.field_key} name={this.props.field_key} value={this.state.current_option}/>
        <div id={this.props.field_key + '-error'}/>
      </div>
    );
  }

}
