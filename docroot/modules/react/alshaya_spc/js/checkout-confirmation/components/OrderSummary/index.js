import React from 'react';
import OrderSummaryItem from "../OrderSummaryItem";

export default class OrderSummary extends React.Component {
  render() {
    let customerName = Drupal.t('Kimi Raikkonen');
    let customerAddress = Drupal.t('Salmiya, Block 10, Al-Adsane St, Building 33, Floor 3, Apartment 306');
    return (
      <div className='spc-order-summary'>
        <div className='spc-order-summary-order-preview'>
          <OrderSummaryItem label={Drupal.t('Corfimation email sent to')} value={Drupal.t('kimiraikkone@gmail.com')}/>
          <OrderSummaryItem label={Drupal.t('Order number')} value={Drupal.t('HMKWHDE0000007')}/>
          <OrderSummaryItem label={Drupal.t('Transaction ID')} value={Drupal.t('1234567890')}/>
        </div>
        <div className='spc-order-summary-order-detail'>
          <input type='checkbox' id='spc-detail-open'/>
          <label for='spc-detail-open'>{Drupal.t('Order Detail')}</label>
          <div className='spc-detail-content'>
            <OrderSummaryItem type='address' label={Drupal.t('Delivery to')} name={customerName} address={customerAddress}/>
            <OrderSummaryItem label={Drupal.t('Mobile Number')} value={Drupal.t('+965 1234 1234')}/>
            <OrderSummaryItem label={Drupal.t('Payment method')} value={Drupal.t('Credit Card')}/>
            <OrderSummaryItem label={Drupal.t('Delivery type')} value={Drupal.t('Home Delivery')}/>
            <OrderSummaryItem label={Drupal.t('Expected delivery within')} value={Drupal.t('1-2 days')}/>
            <OrderSummaryItem label={Drupal.t('Number of items')} value={Drupal.t('999')}/>
          </div>
        </div>
      </div>
    );
  }
}
