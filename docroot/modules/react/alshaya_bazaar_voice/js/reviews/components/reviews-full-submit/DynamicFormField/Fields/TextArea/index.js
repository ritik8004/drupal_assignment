import React from 'react';

class TextArea extends React.Component {
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
      text,
    } = this.props;
    const { errors } = this.state;

    let focusClass = '';
    if (defaultValue !== undefined && defaultValue !== '') {
      focusClass = 'focus';
    }

    return (
      <>
        {text !== undefined
          && (
          <div className="head-row">{text}</div>
          )}
        <div className="write-review-type-textarea">
          <label>{label}</label>
          <textarea
            required={required}
            id={id}
            name={id}
            className={focusClass}
            onChange={(e) => this.handleChange(e, minLength)}
            minLength={minLength}
            maxLength={maxLength}
          >
            {defaultValue}
          </textarea>
          <div className="c-input__bar" />
          <div id={`${label}-error`} className="error">{errors[id]}</div>
        </div>
      </>
    );
  }
}

export default TextArea;
