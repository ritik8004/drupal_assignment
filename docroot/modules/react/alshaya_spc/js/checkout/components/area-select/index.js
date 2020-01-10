import React from 'react';

import Select from 'react-select';
import axios from 'axios';

export default class AreaSelect extends React.Component {

  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  // Get area list.
  getAreaList = (key, field) => {
    let data = new Array();
    let address_fields = window.drupalSettings.address_fields;
    // If no area parent to select.
    if (address_fields.area_parent === undefined) {
      data[0] = {
          value: '',
          label: Drupal.t('Select @field', {'@field': field.label})
      }
      Object.entries(window.drupalSettings.area_list).forEach(([tid, tname]) => {
        data[tid] = {
          value: tid,
          label: tname,
        };
      });
    }

    return data;
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.remove('open');
  };

  render() {
    let options = this.getAreaList(this.props.field_key, this.props.field);
    if (this.props.area_list !== null) {
      options = this.props.area_list;
    }

    return (
        <div>
          <label>
           {this.props.field.label}
          </label>
          <Select
            ref={this.selectRef}
            name={this.props.field_key}
            classNamePrefix='spcSelect'
            className={'spc-select'}
            onMenuOpen={this.onMenuOpen}
            onMenuClose={this.onMenuClose}
            onChange={this.handleChange}
            options={options}
            value={options[1]}
            defaultValue={options[1]}
            isSearchable={true}
          />
          <div id={this.props.field_key + '-error'}></div>
        </div>
    );
  }

}
