import React from 'react'
import parse from 'html-react-parser';
import StoreItem from '../store-item';

const StoreItemInfoWindow = ({ store }) => {
  return (
    <>
      <StoreItem store={store} />
    </>
  );
}

export default StoreItemInfoWindow;
