import React from 'react';
import Loading from '../../../utilities/loading';

const AuraCheckoutRedeem = React.lazy(() => import(/* webpackChunkName: 'aura-checkout' */ './index'));

const AuraCheckoutContainer = (props) => {
  const { cart } = props;
  return (
    <React.Suspense fallback={<Loading />}>
      <AuraCheckoutRedeem
        cart={cart}
        animationDelay="0.4s"
      />
    </React.Suspense>
  );
};

export default AuraCheckoutContainer;
