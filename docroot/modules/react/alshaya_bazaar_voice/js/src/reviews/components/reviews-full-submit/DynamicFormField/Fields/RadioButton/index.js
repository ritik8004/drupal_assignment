import React from 'react';
import ConditionalView from '../../../../../../common/components/conditional-view';
import getStringMessage from '../../../../../../../../../js/utilities/strings';

class RadioButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeId: '',
      swithcClass: '',
    };
  }

  setActiveId = (radioValue, radioLabel) => {
    if (radioValue === 1) {
      this.setState({ activeId: 'no', swithcClass: radioLabel });
    } else {
      this.setState({ activeId: 'yes', swithcClass: radioLabel });
    }
  };

  render() {
    const {
      required,
      id,
      label,
      text,
    } = this.props;
    const { activeId, swithcClass } = this.state;
    const recommend = {
      0: getStringMessage('no'),
      1: getStringMessage('yes'),
    };

    return (
      <>
        <ConditionalView condition={text !== undefined}>
          <div id={`${id}-head-row`} className="head-row">{text}</div>
        </ConditionalView>
        <div id={id} className="switch-button">
          <div className="switch-text query">
            <label htmlFor={label}>
              {label}
              {' '}
              {(required) ? '*' : '' }
            </label>
          </div>
          <div className="switch-text answer" id={`${id}-error`}>
            {Object.values(recommend).reverse().map((radioLabel, i) => {
              const radioValue = i;
              return (
                <React.Fragment key={radioValue}>
                  <span
                    className={swithcClass === radioLabel ? 'switchOn' : 'switchOff'}
                    onClick={() => this.setActiveId(radioValue, radioLabel)}
                    htmlFor={radioLabel}
                  >
                    {radioLabel}
                  </span>
                </React.Fragment>
              );
            })}
          </div>
          <input type="hidden" id={id} name={id} required={required} value={activeId || ''} />
        </div>
      </>
    );
  }
}

export default RadioButton;
