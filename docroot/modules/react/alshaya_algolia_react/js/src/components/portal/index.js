import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

const Portal = ({
  query, children, conditional = false, ...attr
}) => {
  const el = useRef(document.createElement('div'));
  const [dynamic] = useState(!el.current.parentElement);
  useEffect(() => {
    const createPortalCheck = (conditional === true) ? (query !== '') : true;
    const refElement = el.current;
    if (dynamic && createPortalCheck) {
      Object.entries(attr).forEach(([property, value]) => { refElement[property] = value; });
      const autosuggestContainer = document.querySelector('.react-autosuggest__container');
      autosuggestContainer.appendChild(refElement);
    }
    return () => {
      if (dynamic && refElement.parentElement) {
        refElement.parentElement.removeChild(refElement);
      }
    };
  }, [attr, conditional, dynamic, query]);

  return createPortal(children, el.current);
};

export default Portal;
