import React from 'react';

class Checkbox extends React.Component {
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
      text,
    } = this.props;

    return (
      <>
        {text !== undefined
          && (
          <div className="head-row">{text}</div>
          )}
        <div className="write-review-type-checkbox">
          <input
            type="checkbox"
            id={id}
            name={id}
            required={required}
            onBlur={(e) => this.handleEvent(e, 'blur')}
          />
          <label>
            {label}
            {' '}
            {(required) ? '*' : '' }
          </label>
        </div>
      </>
    );
  }
}

export default Checkbox;
