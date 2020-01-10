import React from 'react';

import Select from 'react-select';
import axios from 'axios';

export default class AreaSelect extends React.Component {

  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
  }

  // Get parent area list.
  getAreaList = (key, field) => {
    let data = new Array();
    data[0] = {
      value: '',
      label: Drupal.t('Select @field', {'@field': field.label})
    }
    Object.entries(window.drupalSettings.area_parent_list).forEach(([tid, tname]) => {
      data[tid] = {
        value: tid,
        label: tname,
      };
    });

    return data;
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.remove('open');
  };

  handleChange = (selectedOption) => {
    // Only on change of area_parent, get area list.
    if (this.props.field_key === 'area_parent') {
      var api_url = 'area-list/' + selectedOption.value;

      return axios.get(api_url)
        .then(response => {
          this.props.areasUpdate(response.data);
      })
      .catch(error => {
        // Processing of error here.
      });
    }
  };

  render() {
    let options = this.getAreaList(this.props.field_key, this.props.field);

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
