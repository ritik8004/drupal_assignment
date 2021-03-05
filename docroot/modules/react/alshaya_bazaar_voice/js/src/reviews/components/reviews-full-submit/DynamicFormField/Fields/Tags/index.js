import React from 'react';

class Tags extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      tagVal: '',
    };
  }

  handleClick = (e) => {
    this.setState({
      tagVal: e.target.checked,
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
    const { tagVal } = this.state;

    return (
      <>
        {text !== undefined
          && (
          <div className="head-row">{text}</div>
          )}
        <div className="write-review-type-tags">
          <input
            type="checkbox"
            defaultValue={(tagVal !== '') ? tagVal : defaultValue}
            id={id}
            name={id}
            required={required}
            onClick={(e) => this.handleClick(e)}
          />
          <label htmlFor={id}>
            {label}
            {' '}
            {(required) ? '*' : '' }
          </label>
        </div>
      </>
    );
  }
}

export default Tags;
