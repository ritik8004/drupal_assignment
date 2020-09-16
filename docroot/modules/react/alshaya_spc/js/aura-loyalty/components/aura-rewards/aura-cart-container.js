import React from 'react';
import Loading from '../../../utilities/loading';

const AuraRewards = React.lazy(() => (import(/* webpackChunkName: 'aura-cart' */ '.')));

const AuraCartContainer = () => (
  <React.Suspense fallback={<Loading />}>
    <AuraRewards />
  </React.Suspense>
);

export default AuraCartContainer;
