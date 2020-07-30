import React from 'react';
import Loading from '../../../utilities/loading';

const ClickCollect = React.lazy(async () => {
  // Wait for fetchstore request to finish, before
  // We show click n collect with map.
  await new Promise((resolve) => {
    const interval = setInterval(() => {
      if (window.fetchStore === 'finished') {
        clearInterval(interval);
        resolve();
      }
    }, 500);
  });
  return import('./click-collect');
});

const ClickCollectContainer = ({ closeModal, openSelectedStore }) => (
  <React.Suspense fallback={<Loading />}>
    <ClickCollect
      closeModal={closeModal}
      openSelectedStore={openSelectedStore}
    />
  </React.Suspense>
);

export default ClickCollectContainer;
