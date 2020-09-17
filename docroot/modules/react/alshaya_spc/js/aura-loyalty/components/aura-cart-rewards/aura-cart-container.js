import React from 'react';
import Loading from '../../../utilities/loading';

const AuraCartRewards = React.lazy(() => import(/* webpackChunkName: 'aura-cart' */ './index'));

const AuraCartContainer = () => (
  <React.Suspense fallback={<Loading />}>
    <AuraCartRewards />
  </React.Suspense>
);

export default AuraCartContainer;
