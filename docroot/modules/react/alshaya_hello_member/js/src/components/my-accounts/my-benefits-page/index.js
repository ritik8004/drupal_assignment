import HTMLReactParser from 'html-react-parser';
import moment from 'moment';
import React from 'react';
import Loading from '../../../../../../js/utilities/loading';

class MyBenefitsPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myBenefit: null,
    };
  }

  async componentDidMount() {
    // --TODO-- API integration task to be started once we have api from MDC.
    // input param -- drupalSettings.hmBenefits.code
    const myBenefit = {
      offers:
        {
          name: 'Member discount!',
          category_name: 'Marketing offer',
          code: 'BUY2GET1FREE_TST',
          short_description: 'Shop and get a product for free!',
          description: "<html style=\"width: 100%;height: 100%;margin: 0;padding: 0;\" class=\"mte-created-with\"><head class=\"mte-not-processable-element\">\r\n\r\n</head>\r\n\r\n<body style=\"width: 100%;height: 100%; margin: 0;\" data-new-gr-c-s-check-loaded=\"14.1062.0\" data-gr-ext-installed=\"\">\r\n    <div class=\"mte-not-deletable-element\" style=\"width: 100%;min-height: 10%;background: #FFFFFF; overflow: hidden;\">\r\n        <div class=\"benefits_content en\">\r\n            <p>We are introducing a new way of rewarding you as a member. You will receive Conscious points every time you purchase a conscious item or make conscious choices such as recycling your clothes or bringing your own shopping bag to the store.</p>\r\n            <h3>What:</h3>\r\n            <p>Collect Conscious points by:</p>\r\n            <ul>\r\n                <li>Bringing your own bag when you shop in store: 5 points</li>\r\n                <li>Recycling clothes you no longer use: earn a digital Bonus Voucher with 15% off an item &amp; 20 points</li>\r\n                <li>Purchasing Conscious products: Earn points.In store, all products marked with a green hangtag are conscious. At hm.com, they’re simply marked with the word “conscious”.</li>\r\n            </ul>\r\n            <h3>How:</h3>\r\n            <p>With your Conscious points separated from your regular points, it'll be easier for you to follow your conscious actions. Your Conscious points will transform into vouchers, redeemable with your next purchase. (Remember to look out for the Conscious marker and continue to make more sustainable choices). Check your Conscious statement on your Points history page under My Account.</p>\r\n        </div>\r\n        <div class=\"benefits_disclaimer en\">\r\n            <p>Maximum one code per member, which can be used at one occassion for purchase of above stated models, directly from the online store in the UK. For more terms, please visit https://www2.hm.com/en BUY2GET1FREETEST</p>\r\n        </div>\r\n    </div>\r\n\r\n\r\n<grammarly-desktop-integration data-grammarly-shadow-root=\"true\"></grammarly-desktop-integration>\r\n\r\n</body></html>",
          temrs_and_conditions: 'Terms and condition text',
          start_date: '2022-05-26T14:52:44Z',
          end_date: '2022-06-30',
          small_image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
          large_image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
          status: 'Active',
          member_identifier: '0000000833455',
        },
      message: null,
      error: null,
    };

    this.setState({
      wait: true,
      myBenefit,
    });
  }

  render() {
    const { wait, myBenefit } = this.state;

    if (!wait && myBenefit === null) {
      return (
        <div className="my-benefit-page-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <div className="my-benefit-page-wrapper">
        <div className="image-container">
          <img src={myBenefit.offers.small_image} />
        </div>
        <div className="voucher-wrapper">
          <div className="title">
            {myBenefit.offers.name}
          </div>
          <div className="info">
            {myBenefit.offers.short_description}
          </div>
          <div className="expiry">
            {moment(new Date(myBenefit.offers.end_date)).format('DD MMMM YYYY')}
          </div>
        </div>
        <div>
          <div>View QR Code</div>
          <div>Add to bag</div>
        </div>
        <div>
          {HTMLReactParser(myBenefit.offers.description)}
        </div>
        <div>
          {myBenefit.offers.temrs_and_conditions}
        </div>
      </div>
    );
  }
}

export default MyBenefitsPage;
