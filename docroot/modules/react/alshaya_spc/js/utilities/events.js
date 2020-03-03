export const dispatchCustomEvent = (eventName, eventDetail) => {
  const event = new CustomEvent(eventName, {
    bubbles: true,
    detail: eventDetail,
  });
  document.dispatchEvent(event);
};
