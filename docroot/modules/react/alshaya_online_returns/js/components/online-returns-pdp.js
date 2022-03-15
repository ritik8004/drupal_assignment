import React from 'react';

class OnlineReturnsPDP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      eligibleForReturn: true,
    };
  }

  componentDidMount() {
    document.addEventListener('onSkuVariantSelect', this.updateState, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onSkuVariantSelect', this.updateState, false);
  }

  updateState = (variantDetails) => {
    const { data } = variantDetails.detail;

    if (data.length !== 0) {
      this.setState({
        eligibleForReturn: data.eligibleForReturn,
      });
    }

    return null;
  };

  render() {
    const {
      eligibleForReturn,
    } = this.state;

    if (eligibleForReturn) {
      return null;
    }

    return (
      <span>
        { Drupal.t('Not eligible for Return') }
      </span>
    );
  }
}

export default OnlineReturnsPDP;
