import React from 'react';

import FilterList from '../../../utilities/filter-list';

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
    let data = new Array();
    let areas = document.querySelectorAll('[data-list=areas-list]');
    if (areas.length > 0) {
      for (let i = 0; i < areas.length; i++) {
        let id = areas[i].getAttribute('data-parent-id');
        data[id] = {
          value: id,
          label: areas[i].getAttribute('data-parent-label'),
        };
      }

      this.setState({
        areas: data
      });
    }
  }

  // Handle change of 'area_parent' list.
  handleChange = (selectedOption) => {
    this.setState({
      current_option: selectedOption.value
    });

    this.props.areasUpdate(selectedOption.value);
  };

  render() {
    let options = this.state.areas;

    return (
        <div>
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
              placeHolderText={Drupal.t('Select for an city')}
              processingCallback={this.processSelectedItem}
            />
          }
          <input type='hidden' name={this.props.field_key} value={this.state.current_option}/>
          <div id={this.props.field_key + '-error'}></div>
        </div>
    );
  }

}
