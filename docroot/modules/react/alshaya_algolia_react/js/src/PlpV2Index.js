import React from 'react';
import ReactDOM from 'react-dom';
import PlpApp from './plp/PlpApp';

// @todo To check how to delay the rendering.
setTimeout(() => {
  ReactDOM.render(
    <PlpApp />,
    document.querySelector('#alshaya-v2-algolia-plp'),
  );
}, 10000);
