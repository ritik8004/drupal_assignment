import React from 'react';

class Checkbox extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      checkVal: '',
    };
  }

  handleClick = (e) => {
    this.setState({
      checkVal: e.target.checked,
    });
  };

  render() {
    const {
      required,
      id,
      label,
      defaultValue,
      text,
    } = this.props;
    const { checkVal } = this.state;

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
            defaultValue={(checkVal !== '') ? checkVal : defaultValue}
            onClick={(e) => this.handleClick(e)}
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
