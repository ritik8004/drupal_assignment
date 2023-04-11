import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getPriceToHelloMemberPoint, getHelloMemberDictionaryData } from '../../../../../js/utilities/helloMemberHelper';
import logger from '../../../../../js/utilities/logger';

class HelloMemberPDP extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      productPoints: null,
      dictionaryData: null,
    };
  }

  async componentDidMount() {
    // If dictionary data does not exists in storage, we do api call.
    const requestData = {
      type: 'HM_ACCRUAL_RATIO',
      programCode: 'hello_member',
    };
    const response = await getHelloMemberDictionaryData(requestData);
    if (hasValue(response.data) && !hasValue(response.data.error)) {
      this.setState({
        dictionaryData: response.data,
      }, () => {
        this.setInitialProductPoints();
      });
    } else {
      // If coupon details API is returning Error.
      logger.error('Error while calling the dictonary data api, @message', {
        '@message': response.data.error_message,
      });
    }

    // Update hello member points on variant select.
    document.addEventListener('onSkuVariantSelect', this.updateHelloMemberPoints, false);
  }

  componentWillUnmount() {
    document.removeEventListener('onSkuVariantSelect', this.updateHelloMemberPoints, false);
  }

  /**
   * Utility function to set hello member product points for current product.
   */
  setInitialProductPoints = () => {
    // Get the product information from the DOM element.
    const productData = document.querySelector('[gtm-type="gtm-product-link"][gtm-view-mode="full"]');
    if (!productData) {
      return;
    }

    // Get the price from gtm-price tag attribute on page load.
    const productPrice = productData.getAttribute('gtm-price') || 0;

    // Return price as hello member points.
    const { dictionaryData } = this.state;
    this.setState({
      productPoints: getPriceToHelloMemberPoint(productPrice, dictionaryData),
    });
  };

  /**
   * Utility function to update hello member points for variant selected.
   */
  updateHelloMemberPoints = (variantDetails) => {
    const { dictionaryData } = this.state;
    const { data } = variantDetails.detail;

    if (data.length !== 0) {
      this.setState({
        productPoints: getPriceToHelloMemberPoint(data.price, dictionaryData),
      });
    }

    return null;
  };

  render() {
    const {
      productPoints,
    } = this.state;

    if (!hasValue(productPoints)) {
      return null;
    }

    return (
      <>
        <div className="hello-member-points">
          <p>{Drupal.t('Members earn @pointValue point(s)', { '@pointValue': productPoints }, { context: 'hello_member' })}</p>
        </div>
      </>
    );
  }
}

export default HelloMemberPDP;
