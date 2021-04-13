import React from 'react';
import ConditionalView from '../../../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../../../js/utilities/strings';

class TextArea extends React.Component {
  handleChange = (e) => {
    const { value, minLength, id } = e.currentTarget;

    if (value.length > 0) {
      document.getElementById(`${id}-error`).innerHTML = value.length < minLength
        ? getStringMessage('text_min_chars_limit_error', { '%minLength': minLength })
        : '';
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
      text,
      placeholder,
    } = this.props;

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div className="head-row">{text}</div>
        </ConditionalView>
        <div className="write-review-type-textarea">
          <label>
            {label}
            {' '}
            {(required) ? '*' : '' }
          </label>
          <textarea
            id={id}
            name={id}
            onChange={(e) => this.handleChange(e)}
            minLength={minLength}
            maxLength={maxLength}
            placeholder={placeholder}
          >
            {defaultValue}
          </textarea>
          <div className="c-input__bar" />
          <div id={`${id}-error`} className="error" />
        </div>
      </>
    );
  }
}

export default TextArea;
