import React from 'react';

const AuraLogo = (props) => {
  const { stacked } = props;
  const basePath = '/themes/custom/transac/alshaya_white_label/imgs/aura/';

  if (stacked === 'vertical') {
    return <img src={`${basePath}aura-logo-vertical.svg`} />;
  }

  return <img src={`${basePath}aura-logo-horizontal.svg`} />;
};

export default AuraLogo;
