export const dispatchCustomEvent = (eventName, eventDetail) => {
  let event = new CustomEvent(eventName, {
    bubbles: true,
    detail: eventDetail
  });
  document.dispatchEvent(event);
};
