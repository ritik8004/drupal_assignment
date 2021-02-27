import React from 'react';
import { validEmailRegex } from '../../../../../../utilities/write_review_util';

class TextField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      errors: [],
    };
  }

  handleChange = (e, minLength) => {
    const { errors } = this.state;
    const { name, value } = e.currentTarget;

    if (value.length > 0) {
      errors[name] = value.length < minLength
        ? Drupal.t('Minimum characters limit for this field is ') + minLength
        : null;
    }
    if (name === 'useremail') {
      errors[name] = validEmailRegex.test(value)
        ? null
        : Drupal.t('Email address is not valid.');
    }
    this.setState({ errors, [name]: value });
  };

  render() {
    const {
      required,
      id,
      label,
      defaultValue,
      maxLength,
      minLength,
      visible,
      text,
      classLable,
    } = this.props;
    const { errors } = this.state;

    if (visible === true) {
      return (
        <>
          {text !== undefined
            && (
            <div className="head-row">{text}</div>
            )}
          <div className={`write-review-type-textfield ${(classLable !== undefined) ? classLable : ''}`}>
            <input
              type="text"
              id={id}
              name={id}
              defaultValue={defaultValue}
              onChange={(e) => this.handleChange(e, minLength)}
              maxLength={maxLength}
              minLength={minLength}
              required={required}
            />
            <div className="c-input__bar" />
            <label>{label}</label>
            <div id={`${label}-error`} className="error">{errors[id]}</div>
          </div>
        </>
      );
    }
    return (
      <input
        type="text"
        id={id}
        name={id}
        hidden
      />
    );
  }
}

export default TextField;
