import React from 'react';
import getStringMessage from '../../../../../../../../../js/utilities/strings';
import ConditionalView from '../../../../../../common/components/conditional-view';
import { validateInputLang } from '../../../../../../utilities/write_review_util';

class TextArea extends React.Component {
  handleChange = (e) => {
    const { label } = this.props;
    const { value, minLength, id } = e.currentTarget;

    if (value.length > 0 && !validateInputLang(value)) {
      document.getElementById(`${id}-error`).innerHTML = getStringMessage('text_input_lang_error');
      document.getElementById(id).classList.add('error');
    } else if (value.length > 0 && value.length < minLength) {
      document.getElementById(`${id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': minLength, '%fieldTitle': label });
      document.getElementById(id).classList.add('error');
    } else if (value.length === 0) {
      document.getElementById(`${id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': label });
      document.getElementById(id).classList.add('error');
    } else {
      document.getElementById(`${id}-error`).innerHTML = '';
      document.getElementById(id).classList.remove('error');
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
          <div id={`${id}-head-row`} className="head-row">{text}</div>
        </ConditionalView>
        <div id={`${id}-perror`} className="write-review-type-textarea">
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
          <div id={`${id}-error`} className={(required) ? 'error' : ''} />
        </div>
      </>
    );
  }
}

export default TextArea;
