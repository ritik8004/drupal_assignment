import React from 'react';
import NotEligibleForReturn from '../not-eligible-for-return';
import EligibleForReturn from '../eligible-for-return';

class OnlineReturnsPDP extends React.Component {
  constructor(props) {
    const { eligibleForReturn } = props;
    super(props);
    this.state = {
      eligibleForReturn,
    };
  }

  componentDidMount() {
    document.addEventListener('onSkuVariantSelect', this.updateState, false);
    document.addEventListener('onSkuBaseFormLoad', this.updateState, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onSkuVariantSelect', this.updateState, false);
    document.removeEventListener('onSkuBaseFormLoad', this.updateState, false);
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
      return <EligibleForReturn />;
    }

    return <NotEligibleForReturn />;
  }
}

export default OnlineReturnsPDP;
