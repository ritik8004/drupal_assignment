import React from "react";
import Loading from "../../../utilities/loading";

let ClickCollect = React.lazy(async () => {
  // Wait for fetchstore request to finish, before
  // We show click n collect with map.
  await new Promise((resolve, reject) => {
    let interval = setInterval(() => {
      if (window.fetchStore != 'pending') {
        clearInterval(interval);
        resolve();
      }
    }, 500);
  });
  return import("./click-collect");
});

const ClickCollectContainer = ({closeModal, openSelectedStore}) => {
  return (
    <React.Suspense fallback={<Loading/>}>
      <ClickCollect closeModal={closeModal} openSelectedStore={openSelectedStore}/>
    </React.Suspense>
  )
}

export default ClickCollectContainer;
