import React from 'react';
import ConditionalView from '../../../../../../common/components/conditional-view';

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
        <ConditionalView condition={text !== undefined}>
          <div className="head-row">{text}</div>
        </ConditionalView>
        <div className="write-review-type-checkbox" id={`${id}-error`}>
          <input
            type="checkbox"
            id={id}
            name={id}
            required={required}
            defaultValue={(checkVal !== '') ? checkVal : defaultValue}
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

export default Checkbox;
