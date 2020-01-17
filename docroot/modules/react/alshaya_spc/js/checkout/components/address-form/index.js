import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import DynamicFormField from '../dynamic-form-field';
import FixedFields from '../fixed-fields';
import {fixedFieldValidation} from '../fixed-fields/validation';

export default class AddressForm extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      'area_list': null
    };
  }

  // Submit handler for form.
  handleSubmit = (e) => {
    e.preventDefault();
    this.validateForm(e);
  }

  refreshAreas = (area_list) => {
    let data = new Array();
    Object.entries(area_list).forEach(([tid, tname]) => {
      data[tid] = {
        value: tid,
        label: tname,
      };
    });

    this.setState({
      area_list: data
    });
  }

  // Validation handler for the form.
  validateForm = (e) => {
  	// Validation for fixed fields.
  	let valid_form = fixedFieldValidation(e);

    // Validation for dynamic fields.
  	Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      // If field is required.
      if (field.required === true ||
        (key === 'administrative_area' || key === 'area_parent')) {
        let ele_val = e.target.elements[key].value;
        if (ele_val.trim().length === 0){
          document.getElementById(key + '-error').innerHTML = Drupal.t('Please add @field.', {'@field': field.label});
          document.getElementById(key + '-error').classList.add('error');
          valid_form = false;
        }
        else {
          // Remove error class and any error message.
          document.getElementById(key + '-error').innerHTML = '';
          document.getElementById(key + '-error').classList.remove('error');
        }
      }
    });

  	// If there is any validation fail for form.
  	if (!valid_form) {
  	  return;
  	}
  };

  render() {
  	let dynamicFields = [];
    Object.entries(window.drupalSettings.address_fields).forEach(([key, field]) => {
      dynamicFields.push(<DynamicFormField areasUpdate={this.refreshAreas} area_list={this.state.area_list} field_key={key} field={field}/>);
    });

    return(
      <div>
        <SectionTitle>{Drupal.t('Delivery information')}</SectionTitle>
        <div>{Drupal.t('Deliver to my location')}</div>
      	<form onSubmit={this.handleSubmit}>
          {dynamicFields}
          <FixedFields />
          <input type="submit" value={Drupal.t('Save')} />
      </form>
      </div> 
    );
  }

}
