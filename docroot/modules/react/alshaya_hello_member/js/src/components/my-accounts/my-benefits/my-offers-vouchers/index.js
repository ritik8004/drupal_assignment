import React from 'react';
import moment from 'moment';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { isMobile } from '../../../../../../../js/utilities/display';

const { showMoreLimit } = drupalSettings.helloMember;
class MyOffersAndVouchers extends React.Component {
  constructor(props) {
    super(props);
    const display = isMobile();
    const viewAllBenefits = new URLSearchParams(window.location.search).get('view-all-benefits');

    this.state = {
      expanded: viewAllBenefits || display,
    };
  }

  getShowBenefitsLink = () => {
    const { currentPath } = drupalSettings.path;
    const { uid } = drupalSettings.user;
    const { myBenefitsList } = this.props;

    if (currentPath === 'home') {
      return (
        <a className="view-all-benefits" href={`${Drupal.url(`user/${uid}?view-all-benefits=true`)}`}>
          {getStringMessage('view_all_benefits')}
        </a>
      );
    }
    if (myBenefitsList.length > showMoreLimit) {
      const { expanded } = this.state;
      return (
        <a onClick={() => this.setState({
          expanded: !expanded,
        })}
        >
          {expanded ? getStringMessage('show_less') : getStringMessage('show_all')}
        </a>
      );
    }
    return '';
  };

  render() {
    const { myBenefitsList } = this.props;
    const { expanded } = this.state;
    const dataForDisplay = expanded ? myBenefitsList : myBenefitsList.slice(0, showMoreLimit);
    const { uid } = drupalSettings.user;
    return (
      <div className="my-benefits-wrapper">
        {dataForDisplay.map((data) => (
          <a className="my-offers-vouchers-details" key={data.id || data.code} href={`${Drupal.url(`user/${uid}`)}/hello-member-benefits/${hasValue(data.id) ? `coupon/${data.id}` : `offer/${data.code}`}`}>
            <div className="image-container">
              <img src={data.small_image} />
            </div>
            <div className="voucher-wrapper">
              <div className="title">
                {data.category_name}
              </div>
              <div className="info">
                {data.description}
              </div>
              <div className="expiry">
                {getStringMessage('benefit_expire', { '@expire_date': moment(new Date(data.expiry_date || data.end_date)).format('DD MMMM YYYY') })}
              </div>
            </div>
          </a>
        ))}
        <div className="btn-wrapper">
          { this.getShowBenefitsLink()}
        </div>
      </div>
    );
  }
}

export default MyOffersAndVouchers;
