import React from 'react';

class NetPromoter extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promoterVal: '',
    };
  }

  handleClick = (e) => {
    const { value } = e.currentTarget;

    this.setState({ promoterVal: value });
  };

  render() {
    const {
      required,
      id,
      label,
      maxLength,
      text,
    } = this.props;

    const { promoterVal } = this.state;

    return (
      <>
        {text !== undefined
          && (
          <div className="head-row">{text}</div>
          )}
        <div className="netpromotr-wrapper">
          <div className="netpromoter-label">
            <label htmlFor={label}>{label}</label>
          </div>
          <div className="netpromoter-option">
            <div className="survey-block">
              {[...Array(maxLength)].map((radio, i) => {
                const radioIndex = i + 1;
                return (
                  <div key={radioIndex} className="form-type-radio">
                    <input
                      type="radio"
                      id={i}
                      value={i}
                      name="netpromoter"
                      data-drupal-selector={i}
                      onClick={(e) => this.handleClick(e)}
                    />
                    <label htmlFor={i}><p>{i}</p></label>
                  </div>
                );
              })}
            </div>
            <div className="survey-experience">
              <div>{Drupal.t('Not at all likely')}</div>
              <div>{Drupal.t('Extremely likely')}</div>
            </div>
          </div>
          <input type="hidden" id={id} name={id} required={required} value={promoterVal || ''} />
          <div className="c-input__bar" />
          <div id="bv-error" className="error" />
        </div>
      </>
    );
  }
}

export default NetPromoter;
