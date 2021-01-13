import React from 'react';
import Loading from '../../../utilities/loading';

const AuraCheckoutRedeem = React.lazy(() => import(/* webpackChunkName: 'aura-checkout' */ './index'));

const AuraCheckoutContainer = (props) => {
  const { cartId, price, totals } = props;
  return (
    <React.Suspense fallback={<Loading />}>
      <AuraCheckoutRedeem
        cartId={cartId}
        price={price}
        totals={totals}
        animationDelay="0.4s"
      />
    </React.Suspense>
  );
};

export default AuraCheckoutContainer;
