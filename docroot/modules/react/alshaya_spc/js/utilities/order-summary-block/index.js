import React from 'react';
import CheckoutSectionTitle from "../../cart/components/spc-checkout-section-title";
import TotalLineItems from "../../cart/components/total-line-items";

class OrderSummaryBlock extends React.Component {
 render() {
   return (
     <div className="spc-order-summary-block">
       <CheckoutSectionTitle>{Drupal.t('order summary')}</CheckoutSectionTitle>
       <div className="block-content">
         <div className="products"/>
         <TotalLineItems totals={this.props.totals}/>
       </div>
     </div>
   );
 }
}

export default OrderSummaryBlock;
