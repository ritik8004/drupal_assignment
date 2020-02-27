import React from 'react';

import axios from 'axios';
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
      'areas': [],
      'current_option': current_option,
      'showFilterList': false
    };
  }

  componentDidMount() {
    this.getAreaList();
  }

  /**
   * Whether filter list component need to shown or not.
   */
  toggleFilterList = () => {
    this.setState({
      showFilterList: !this.state.showFilterList
    });
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
      return axios.get('areas')
      .then(response => {
        let data = new Array();
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
  };

  render() {
    let options = this.state.areas;
    if (this.props.area_list !== null) {
      options = this.props.area_list;
    }

    if (options.length === 0) {
      return(null);
    }

    let panelTitle = Drupal.t('select ') + this.props.field.label;

    return (
      <div className='spc-type-select'>
        <label>{this.props.field.label}</label>
        {this.state.current_option.length !== 0 ? (
          <div className='spc-area-select-selected' onClick={() => this.toggleFilterList()}>
            {options[this.state.current_option]['label']}
          </div>
        ) : (
          <div className='spc-area-select-selected' onClick={() => this.toggleFilterList()}>
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
        <input type='hidden' name={this.props.field_key} value={this.state.current_option}/>
        <div id={this.props.field_key + '-error'}/>
      </div>
    );
  }

}
