import React from 'react';
import Loading from '../../../utilities/loading';

const AuraCheckoutRedeem = React.lazy(() => import(/* webpackChunkName: 'aura-checkout' */ './index'));

const AuraCheckoutContainer = () => (
  <React.Suspense fallback={<Loading />}>
    <AuraCheckoutRedeem />
  </React.Suspense>
);

export default AuraCheckoutContainer;
