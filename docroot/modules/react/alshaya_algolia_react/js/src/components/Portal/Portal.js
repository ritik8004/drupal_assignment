import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

const Portal = ({query, children, ...attr}) => {
  const el = useRef(document.createElement('div'));
  const [dynamic] = useState(!el.current.parentElement);
  useEffect(() => {
    if (dynamic) {
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
