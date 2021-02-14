import React from 'react';

class TextField extends React.Component {
  handleEvent = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
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
    } = this.props;
    let focusClass = '';
    if (defaultValue !== undefined && defaultValue !== '') {
      focusClass = 'focus';
    }

    if (visible === true) {
      return (
        <div className="write-review-type-textfield">
          <input
            type="text"
            id={id}
            name={id}
            defaultValue={defaultValue}
            onBlur={(e) => this.handleEvent(e, 'blur')}
            className={focusClass}
            maxLength={maxLength}
            minLength={minLength}
            required={required}
          />
          <div className="c-input__bar" />
          <label>{label}</label>
          <div id={`${label}-error`} className="error" />
        </div>
      );
    }
    return (
      <input
        type="text"
        id={id}
        name={id}
        required={required}
        hidden
      />
    );
  }
}

export default TextField;
