import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

const Portal = ({query, children, conditional = false, ...attr}) => {
  const el = useRef(document.createElement('div'));
  const [dynamic] = useState(!el.current.parentElement);
  useEffect(() => {
    const createPortal = (conditional === true) ? (query !== '') : true;

    if (dynamic && createPortal) {
      for (const [property, value] of Object.entries(attr)) {
        el.current[property] = value;
      }
      const autosuggestContainer = document.querySelector('.react-autosuggest__container');
      autosuggestContainer.appendChild(el.current);
    }
    return () => {
      if (dynamic && el.current.parentElement) {
        el.current.parentElement.removeChild(el.current);
      }
    };
  }, [query]);

  return createPortal(children, el.current);
};

export default Portal;
