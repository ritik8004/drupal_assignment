import React from 'react';

class RadioButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeId: '',
    };
  }

  setActiveId = (radioLabel) => {
    if (radioLabel.length > 0) {
      this.setState({
        activeId: radioLabel,
      });
    }
  };

  render() {
    const {
      required,
      id,
      label,
      text,
    } = this.props;
    const { activeId } = this.state;
    const recommend = {
      0: 'No',
      1: 'Yes',
    };

    return (
      <>
        {text !== undefined
          && (
          <div className="head-row">{text}</div>
          )}
        <div className="switch-button">
          <div className="switch-text query">
            <label htmlFor={label}>{label}</label>
          </div>
          <div className="switch-text">
            {Object.values(recommend).reverse().map((radioLabel, i) => {
              const radioValue = i;
              return (
                <React.Fragment key={radioValue}>
                  <span
                    className={activeId === radioLabel ? 'switchOn' : 'switchOff'}
                    onClick={() => this.setActiveId(radioLabel)}
                    htmlFor={radioLabel}
                  >
                    {radioLabel}
                  </span>
                </React.Fragment>
              );
            })}
          </div>
          <input type="hidden" id={id} name={id} required={required} value={activeId || ''} />
          <div className="c-input__bar" />
          <div id="bv-error" className="error" />
        </div>
      </>
    );
  }
}

export default RadioButton;
