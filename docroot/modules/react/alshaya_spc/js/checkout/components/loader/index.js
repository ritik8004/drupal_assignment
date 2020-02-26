import React, {useEffect} from 'react'
import { showLoader, removeLoader } from '../../../utilities/checkout_util';

const Loader = () => {

  React.useEffect(() => {
    showLoader();

    return () => {
      removeLoader();
    };
  })

  return (
    <div />
  )
}

export default Loader;
