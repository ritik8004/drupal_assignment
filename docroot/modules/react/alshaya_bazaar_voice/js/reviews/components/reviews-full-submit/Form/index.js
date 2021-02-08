import React from 'react';
import {
  TextField, TextArea, Select, Checkbox,
} from '../Fields';

export default class Form extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  handleSubmit = (event) => {
    event.preventDefault();
    // console.log(fieldId);
    // console.log(value);
  }

  // fieldChanged = (fieldId, value) => {
  //   // console.log(fieldId);
  //   // console.log(value);
  // };

  render() {
    const {
      formData,
    } = this.props;
    return (
      <form onSubmit={this.handleSubmit} noValidate>
        <h2>{Drupal.t('Write a review')}</h2>
        {Object.keys(formData).map((fieldData) => {
          switch (formData[fieldData]['#type']) {
            case 'textfield':
              return (
                <TextField
                  field={formData[fieldData]}
                  fieldChanged={this.fieldChanged}
                  value={formData[fieldData]['#value']}
                  key={formData[fieldData]['#id']}
                />
              );
            case 'textarea':
              return (
                <TextArea
                  field={formData[fieldData]}
                  fieldChanged={this.fieldChanged}
                  value={formData[fieldData]['#value']}
                  key={formData[fieldData]['#id']}
                />
              );
            case 'select':
              return (
                <Select
                  field={formData[fieldData]}
                  fieldChanged={this.fieldChanged}
                  value={formData[fieldData]['#value']}
                  key={formData[fieldData]['#id']}
                />
              );
            case 'checkbox':
              return (
                <Checkbox
                  field={formData[fieldData]}
                  fieldChanged={this.fieldChanged}
                  value={formData[fieldData]['#value']}
                  key={formData[fieldData]['#id']}
                />
              );
            default:
              return (
                <div>{Drupal.t('No field type is found.')}</div>
              );
          }
        })}
        <div className="cancel">
          <button type="button">{Drupal.t('Cancel')}</button>
        </div>
        <div className="preview">
          <button type="submit">{Drupal.t('Preview')}</button>
        </div>
      </form>
    );
  }
}
