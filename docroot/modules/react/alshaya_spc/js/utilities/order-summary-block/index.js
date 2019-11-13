import React from 'react';
import CheckoutSectionTitle from "../../cart/components/spc-checkout-section-title";
import TotalLineItems from "../../cart/components/total-line-items";

class OrderSummaryBlock extends React.Component {
 render() {
   return (
     <div className="spc-order-summary-block">
       <CheckoutSectionTitle>{Drupal.t('order summary')}</CheckoutSectionTitle>
       <div className="block-content">
         {/*To Be used later on Checkout Delivery pages.*/}
         <div className="products"/>
         <TotalLineItems totals={this.props.totals}/>
         {/*To Be used later on Checkout Delivery pages.*/}
         <div className="actions">
           <div className="checkout-link">
             <a href={Drupal.url('cart')} className="checkout-link">{Drupal.t('continue to checkout')}</a>
           </div>
         </div>
       </div>
     </div>
   );
 }
}

export default OrderSummaryBlock;
