import React from 'react';
import { validEmailRegex } from '../../../../../../utilities/write_review_util';
import ConditionalView from '../../../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../../../js/utilities/strings';

class TextField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      labelActiveClass: '',
    };
  }

  handleChange = (e) => {
    const { value, minLength, id } = e.currentTarget;
    let activeClass = '';
    if (value.length > 0) {
      document.getElementById(`${id}-error`).innerHTML = value.length < minLength
        ? getStringMessage('text_min_chars_limit_error', { '%minLength': minLength })
        : '';
      activeClass = 'active-label';
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
    const { labelActiveClass } = this.state;

    if (visible === true) {
      return (
        <>
          <ConditionalView condition={text !== undefined}>
            <div className="head-row">{text}</div>
          </ConditionalView>
          <div className={`write-review-type-textfield ${(classLable !== undefined) ? classLable : ''}`}>
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
            <label className={`${(defaultValue !== undefined && defaultValue !== null) ? 'active-label' : labelActiveClass}`}>
              {label}
              {' '}
              {(required) ? '*' : '' }
            </label>
            <div id={`${id}-error`} className="error" />
          </div>
        </>
      );
    }
    return (
      <input
        type="text"
        id={id}
        name={id}
        hidden
      />
    );
  }
}

export default TextField;
