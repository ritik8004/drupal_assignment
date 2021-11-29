import React from 'react';
import Loading from '../../../utilities/loading';

const AuraCartRewards = React.lazy(() => import(/* webpackChunkName: 'aura-cart' */ './index'));

const AuraCartContainer = (props) => {
  const { totals } = props;
  return (
    <React.Suspense fallback={<Loading />}>
      <AuraCartRewards totals={totals} />
    </React.Suspense>
  );
};

export default AuraCartContainer;
