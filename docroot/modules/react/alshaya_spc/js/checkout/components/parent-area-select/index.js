import React from 'react';

import Select from 'react-select';
import axios from 'axios';

export default class AreaSelect extends React.Component {

  constructor(props) {
    super(props);
    this.selectRef = React.createRef();
    this.state = {
      'areas': {}
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
  }

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
    var api_url = 'area-list/' + selectedOption.value;
    return axios.get(api_url)
      .then(response => {
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
            value={options[1]}
            defaultValue={options[1]}
            isSearchable={true}
          />
          <div id={this.props.field_key + '-error'}></div>
        </div>
    );
  }

}
