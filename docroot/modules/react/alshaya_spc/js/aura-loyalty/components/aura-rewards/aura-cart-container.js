import React from 'react';
import Loading from '../../../utilities/loading';

const AuraRewards = React.lazy(() => {
  // We show the index file if aura feature is enabled else we show empty
  // component. We do not add aura-empty to the aura chunk so that the chunk
  // size for the empty case remains small.
  if (
    typeof drupalSettings.aura !== 'undefined'
    && drupalSettings.aura.enabled
  ) {
    return import(/* webpackChunkName: 'aura-cart' */ '.');
  }
  return import('../aura-empty');
});

const AuraCartContainer = () => (
  <React.Suspense fallback={<Loading />}>
    <AuraRewards />
  </React.Suspense>
);

export default AuraCartContainer;
