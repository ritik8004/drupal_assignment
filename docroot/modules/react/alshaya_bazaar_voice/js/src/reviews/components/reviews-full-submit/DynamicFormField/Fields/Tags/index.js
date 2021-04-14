import React from 'react';
import ConditionalView from '../../../../../../common/components/conditional-view';

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
      question,
    } = this.props;
    const { tagVal } = this.state;

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div className="head-row">{text}</div>
        </ConditionalView>
        <ConditionalView condition={question !== undefined}>
          <div className="write-review-type-tags-question">{question}</div>
        </ConditionalView>
        <div className="write-review-type-tags" id={`${id}-error`}>
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
