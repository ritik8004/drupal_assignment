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
            <div className="">
              {[...Array(maxLength)].map((i) => (
                <input
                  key={i}
                  type="radio"
                  value={i}
                  name="netpromoter"
                  onClick={(e) => this.handleClick(e)}
                />
              ))}
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
