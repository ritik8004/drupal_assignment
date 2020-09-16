import React from 'react';
import Loading from '../../../utilities/loading';

const AuraRedemption = React.lazy(() => import(/* webpackChunkName: 'aura-checkout' */ '.'));

const AuraCheckoutContainer = () => (
  <React.Suspense fallback={<Loading />}>
    <AuraRedemption />
  </React.Suspense>
);

export default AuraCheckoutContainer;
