import React from 'react';

class TextArea extends React.Component {
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
    } = this.props;

    let focusClass = '';
    if (defaultValue !== undefined && defaultValue !== '') {
      focusClass = 'focus';
    }

    return (
      <div className="write-review-type-textarea">
        <label>{label}</label>
        <textarea
          required={required}
          id={id}
          name={id}
          className={focusClass}
          onBlur={(e) => this.handleEvent(e, 'blur')}
          minLength={minLength}
          maxLength={maxLength}
        >
          {defaultValue}
        </textarea>
        <div className="c-input__bar" />
        <div id={`${label}-error`} className="error" />
      </div>
    );
  }
}

export default TextArea;
