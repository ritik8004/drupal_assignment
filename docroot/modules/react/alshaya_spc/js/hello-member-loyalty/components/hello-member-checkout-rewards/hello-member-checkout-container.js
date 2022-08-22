import React from 'react';
import Loading from '../../../../../js/utilities/loading';

const HelloMemberLoyaltyOptions = React.lazy(() => import('./index'));

const HelloMemberCheckoutContainer = (props) => {
  const { cart, refreshCart } = props;
  return (
    <React.Suspense fallback={<Loading />}>
      <HelloMemberLoyaltyOptions
        cart={cart}
        refreshCart={refreshCart}
        animationDelay="0.4s"
      />
    </React.Suspense>
  );
};

export default HelloMemberCheckoutContainer;
