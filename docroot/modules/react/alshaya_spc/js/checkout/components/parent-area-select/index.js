import React from 'react';

import Select from 'react-select';
import axios from 'axios';

export default class AreaSelect extends React.Component {

  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
    let current_option = new Array();
    // If default value is available, process that.
    if (this.props.default_val.length !== 0
      && this.props.default_val.length !== 'undefined') {
      current_option = this.props.default_val[this.props.field.key];
    }
    this.state = {
      'areas': {},
      'current_option': current_option
    };
  }

  onMenuOpen = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.add('open');
  };

  onMenuClose = () => {
    this.selectRef.current.select.inputRef.closest('.spc-select').classList.remove('open');
  };

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
    return axios.get('parent-areas')
    .then(response => {
      let data = new Array();
      data[0] = {
        value: '',
        label: Drupal.t('Select @field', {'@field': this.props.field.label})
      }
      Object.entries(response.data).forEach(([key, term]) => {
        data[key] = {
          value: key,
          label: term,
        };
      });

      this.setState({
        areas: data
      });
    })
    .catch(error => {
    // Processing of error here.
    });
  }

  // Handle change of 'area_parent' list.
  handleChange = (selectedOption) => {
    this.setState({
      current_option: selectedOption.value
    });

    // Get child areas list.
    var api_url = 'area-list/' + selectedOption.value;
    return axios.get(api_url)
      .then(response => {
        // Refresh child select list.
        this.props.areasUpdate(response.data);
    })
    .catch(error => {
      // Processing of error here.
    });
  };

  render() {
    let options = this.state.areas;

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
            value={options[this.state.current_option]}
            isSearchable={true}
          />
          <div id={this.props.field_key + '-error'}></div>
        </div>
    );
  }

}
