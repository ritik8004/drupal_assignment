import React from 'react';

export default class ValidateEgiftCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  // handle submit.
  handleSubmit = (e) => {
    e.preventDefault();
  }

  render = () => {
    const {
      redeemEgiftCardTitle,
      redeemEgiftCardSubTitle,
      buttonText,
    } = this.props;

    return (
      <div className="egift-wrapper">
        <p><strong>{redeemEgiftCardTitle}</strong></p>
        <p>{redeemEgiftCardSubTitle}</p>

        <div className="egift-form-wrapper">
          <form
            className="egift-validate-form"
            method="post"
            id="egift-val-form"
            onSubmit={this.handleSubmit}
          >
            <div className="spc-type-textfield">
              <input
                type="text"
                name="egift-card-number"
                placeholder="eGift Card Number"
                className="egift-card-number"
              />
              <div id="egift-card-number-error" className="error" />
            </div>
            <div className="field-type-email">
              <input
                type="email"
                name="email"
                placeholder="Email address"
                className="egift-email"
              />
              <div id="egift-email-error" className="error" />
            </div>

            <button
              className="egift-button"
              id="egift-redeem-button"
              type="submit"
            >
              {buttonText}
            </button>
          </form>
        </div>
      </div>
    );
  }
}
