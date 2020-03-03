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
      'areas': {},
      'current_option': current_option,
      'showFilterList': false
    };
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
      current_option: val
    });

    this.handleChange(val);
  }

  componentDidMount() {
    this.getAreasList();
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      // Once we get parent areas list, get corresponding child areas.
      this.handleChange({
        value: this.props.default_val[this.props.field.key]
      });
    }
  }

  /**
   * Get the areas list.
   */
  getAreasList = () => {
    this.setState({
      areas: getAreasList(true, null)
    });
  }

  // Handle change of 'area_parent' list.
  handleChange = (selectedOption) => {
    this.setState({
      current_option: selectedOption
    });

    this.props.areasUpdate(selectedOption);
  };

  render() {
    let options = this.state.areas;
    let panelTitle = Drupal.t('select ') + this.props.field.label;

    return (
        < div className = 'spc-type-select' >
          <label>{this.props.field.label}</label>
            {this.state.current_option.length !== 0 ? (
              <div onClick={() => this.toggleFilterList()}>
                {options[this.state.current_option]['label']}
              </div>
            ) : (
              <div onClick={() => this.toggleFilterList()}>
                {Drupal.t('Select city')}
              </div>
          )}
          {this.state.showFilterList &&
            <FilterList
              selected={options[this.state.current_option]}
              options={options}
              placeHolderText={Drupal.t('search for a city')}
              processingCallback={this.processSelectedItem}
              toggleFilterList={this.toggleFilterList}
              panelTitle={panelTitle}
            />
          }
          <input type='hidden' name={this.props.field_key} value={this.state.current_option}/>
          <div id={this.props.field_key + '-error'}></div>
        </div>
    );
  }

}
