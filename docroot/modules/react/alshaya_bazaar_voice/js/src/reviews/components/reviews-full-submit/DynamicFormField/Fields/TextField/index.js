import React from 'react';
import { validateInputLang, validEmailRegex } from '../../../../../../utilities/write_review_util';
import ConditionalView from '../../../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../../../js/utilities/strings';

class TextField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      labelActiveClass: '',
      currVal: 1,
    };
  }

  handleChange = (e) => {
    const { label } = this.props;
    const { value, minLength, id } = e.currentTarget;
    let activeClass = 'active-label';
    if (value.length > 0
      && !validateInputLang(value)
      && id !== 'usernickname'
      && id !== 'useremail') {
      document.getElementById(`${id}-error`).innerHTML = getStringMessage('text_input_lang_error');
      document.getElementById(id).classList.add('error');
    } else if (value.length > 0 && value.length < minLength) {
      document.getElementById(`${id}-error`).innerHTML = getStringMessage('text_min_chars_limit_error', { '%minLength': minLength, '%fieldTitle': label });
      document.getElementById(id).classList.add('error');
    } else if (value.length === 0) {
      document.getElementById(`${id}-error`).innerHTML = getStringMessage('empty_field_default_error', { '%fieldTitle': label });
      document.getElementById(id).classList.add('error');
      this.setState({ currVal: 0 });
      activeClass = '';
    } else {
      document.getElementById(`${id}-error`).innerHTML = '';
      document.getElementById(id).classList.remove('error');
    }

    this.setState({ labelActiveClass: activeClass });

    if (id === 'useremail') {
      document.getElementById(`${id}-error`).innerHTML = validEmailRegex.test(value)
        ? ''
        : getStringMessage('valid_email_error', { '%mail': value });
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
      text,
      classLable,
      readonly,
    } = this.props;
    let { labelActiveClass } = this.state;
    const { currVal } = this.state;

    if (defaultValue !== null && currVal === 1) {
      labelActiveClass = 'active-label';
    }

    if (visible === true) {
      return (
        <>
          <ConditionalView condition={text !== undefined}>
            <div id={`${id}-head-row`} className="head-row">{text}</div>
          </ConditionalView>
          <div id={`${id}-perror`} className={`write-review-type-textfield ${(classLable !== undefined) ? classLable : ''}`}>
            <input
              type="text"
              id={id}
              name={id}
              defaultValue={defaultValue}
              onChange={(e) => this.handleChange(e)}
              maxLength={maxLength}
              minLength={minLength}
              readOnly={readonly}
            />
            <div className="c-input__bar" />
            <label className={`${(labelActiveClass !== null) ? labelActiveClass : ''}`}>
              {label}
              {' '}
              {(required) ? '*' : '' }
            </label>
            <div id={`${id}-error`} className={(required) ? 'error' : ''} />
          </div>
        </>
      );
    }
    return (
      <input
        type="text"
        id={id}
        name={id}
        defaultValue={defaultValue}
        hidden
      />
    );
  }
}

export default TextField;
