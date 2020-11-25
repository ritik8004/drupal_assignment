import React from 'react';
import Loading from '../../../utilities/loading';

const AuraCartRewards = React.lazy(() => import(/* webpackChunkName: 'aura-cart' */ './index'));

const AuraCartContainer = (props) => {
  const { price } = props;
  return (
    <React.Suspense fallback={<Loading />}>
      <AuraCartRewards price={price} />
    </React.Suspense>
  );
};

export default AuraCartContainer;
