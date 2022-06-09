import React from 'react';
import Loading from '../../../../../../js/utilities/loading';
import MyOffersAndVouchers from './my-offers-vouchers';

class MyBenefits extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: false,
      myBenefitsList: null,
    };
  }

  async componentDidMount() {
    // --TODO-- API integration task to be started once we have api from MDC.
    const myBenefitsList = {
      benefits_list: [
        {
          name: 'Sale at Foot Locker!',
          description: '2 KWD bonus',
          type: 'coupons',
          id: 62556,
          coupon_number: '4712405',
          status: 'active',
          value: '',
          start_date: '2023-05-20',
          end_date: '2023-05-29',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
        {
          name: 'Sale at Foot Locker!',
          description: 'Winter adventures',
          type: 'offers',
          id: 62557,
          coupon_number: '',
          status: 'active',
          value: '1000',
          start_date: '2023-05-20',
          end_date: '2023-06-10',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
        {
          name: 'Sale at Foot Locker!',
          description: 'Winter adventures',
          type: 'offers',
          id: 62558,
          coupon_number: '',
          status: 'active',
          value: '1000',
          start_date: '2023-05-20',
          end_date: '2023-06-10',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
        {
          name: 'Sale at Foot Locker!',
          description: 'Winter adventures',
          type: 'offers',
          id: 62559,
          coupon_number: '',
          status: 'active',
          value: '1000',
          start_date: '2023-05-20',
          end_date: '2023-06-10',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
        {
          name: 'Sale at Foot Locker!',
          description: 'Winter adventures',
          type: 'offers',
          id: 62551,
          coupon_number: '',
          status: 'active',
          value: '1000',
          start_date: '2023-05-20',
          end_date: '2023-06-10',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
        {
          name: 'Sale at Foot Locker!',
          description: 'Winter adventures',
          type: 'offers',
          id: 62552,
          coupon_number: '',
          status: 'active',
          value: '1000',
          start_date: '2023-05-20',
          end_date: '2023-06-10',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
        {
          name: 'Sale at Foot Locker!',
          description: 'Winter adventures',
          type: 'offers',
          id: 62553,
          coupon_number: '',
          status: 'active',
          value: '1000',
          start_date: '2023-05-20',
          end_date: '2023-06-10',
          image: 'https://kw.hm.com/sites/g/files/hm/styles/product_listing/brand/assets-shared/HNM/14042059/c0a20985c2c017db55b80debcd9dbcf2221e98f7/2/8a0d9094e4331fa080d4e429c7e2a0c152b804b1.jpg?itok=KXtJxRNA',
        },
      ],
      message: null,
      error: null,
    };

    this.setState({
      wait: true,
      myBenefitsList,
    });
  }

  render() {
    const { wait, myBenefitsList } = this.state;

    if (!wait && myBenefitsList === null) {
      return (
        <div className="my-benefits-list-wrapper" style={{ animationDelay: '0.4s' }}>
          <Loading />
        </div>
      );
    }

    return (
      <MyOffersAndVouchers myBenefitsList={myBenefitsList.benefits_list} />
    );
  }
}

export default MyBenefits;
