import React from 'react';

class EgiftCardNotLinked extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  // handle submit.
  handleSubmit = (e) => {
    e.preventDefault();
  }

  render() {
    return (
      <div className="egift-notlinked-warpper">
        <div className="egift-notlinked-title">Link my egift card</div>
        <form
          className="egift-validate-form"
          method="post"
          id="egift-val-form"
          onSubmit={this.handleSubmit}
        >
          <div className="egift-textfield">
            <input
              type="text"
              name="egift-card-number"
              placeholder="eGift Card Number"
              className="egift-card-number"
            />
            <div id="egift-card-number-error" className="error" />
          </div>
          <div className="egift-email">
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
            {Drupal.t('Verify')}
          </button>
        </form>
      </div>
    );
  }
}

export default EgiftCardNotLinked;
