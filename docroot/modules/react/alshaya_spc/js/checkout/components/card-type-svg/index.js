import React from 'react';

class CardTypeSVG extends React.Component {
  render() {
    if (this.props.type === 'visa') {
      return (
        <span className={'spc-card-type ' + this.props.class}>
          <svg xmlns="http://www.w3.org/2000/svg" width="52" height="33" viewBox="0 0 52 33">
            <g fill="none" fillRule="evenodd">
              <path fill="#FFF" d="M0 33h52V0H0z"/>
              <path fill="#F7B600" d="M0 33h52v-5H0z"/>
              <path fill="#1A1F71" d="M0 5h52V0H0zM25.566 10.27l-2.65 12.304h-3.204l2.65-12.305h3.204zm13.482 7.944l1.688-4.62.97 4.62h-2.658zm3.578 4.36h2.963L43 10.269h-2.734c-.616 0-1.135.355-1.365.902l-4.808 11.403h3.365l.668-1.838h4.111l.389 1.838zm-8.367-4.018c.015-3.247-4.519-3.427-4.488-4.878.01-.44.433-.91 1.358-1.03.46-.06 1.725-.106 3.161.55l.561-2.61a8.691 8.691 0 0 0-2.997-.544c-3.168 0-5.397 1.672-5.415 4.067-.02 1.772 1.592 2.76 2.804 3.35 1.25.603 1.67.99 1.663 1.528-.009.826-.997 1.191-1.916 1.205-1.613.025-2.547-.433-3.292-.778l-.581 2.698c.75.34 2.131.637 3.562.653 3.368 0 5.57-1.652 5.58-4.21zM20.987 10.27l-5.193 12.305h-3.387l-2.555-9.82c-.155-.604-.29-.826-.761-1.081-.77-.416-2.043-.805-3.162-1.047l.076-.357h5.453c.695 0 1.32.46 1.479 1.254l1.35 7.12 3.333-8.374h3.367z"/>
            </g>
          </svg>
        </span>
      );
    }
    else if (this.props.type === 'mastercard') {
      return (
        <span className={'spc-card-type ' + this.props.class}>
          <svg className='mastercard' xmlns="http://www.w3.org/2000/svg" width="52" height="33" viewBox="0 0 52 33">
            <g fill="none" fillRule="nonzero">
              <path fill="#000" d="M0 0h52v33H0z"/>
              <path fill="#FFF" d="M14.623 30.073v-1.836c0-.704-.428-1.163-1.132-1.163-.367 0-.764.123-1.04.52-.214-.336-.52-.52-.979-.52-.306 0-.642.092-.887.429v-.367h-.581v2.937h.581V28.39c0-.52.306-.765.734-.765.429 0 .673.275.673.765v1.683h.582V28.39c0-.52.305-.765.734-.765.428 0 .673.275.673.765v1.683h.642zm9.545-2.907h-1.07v-.887h-.582v.887h-.612v.52h.612v1.377c0 .673.245 1.07.98 1.07.274 0 .58-.091.795-.213l-.184-.52a1.007 1.007 0 0 1-.55.152c-.307 0-.46-.183-.46-.489v-1.407h1.071v-.49zm5.446-.092a.915.915 0 0 0-.796.429v-.367h-.581v2.937h.581V28.42c0-.49.245-.796.643-.796.122 0 .275.03.397.061l.184-.55a2.066 2.066 0 0 0-.428-.062zm-8.23.306c-.306-.214-.734-.306-1.193-.306-.734 0-1.193.337-1.193.918 0 .49.337.765.979.857l.306.03c.336.062.55.184.55.337 0 .214-.244.367-.703.367-.459 0-.765-.153-.979-.306l-.306.459c.428.306.948.367 1.254.367.857 0 1.316-.398 1.316-.948 0-.52-.367-.765-1.01-.857l-.306-.03c-.275-.031-.52-.123-.52-.306 0-.215.245-.367.582-.367.367 0 .734.152.917.244l.306-.459zm8.872 1.224c0 .887.581 1.53 1.53 1.53.428 0 .734-.092 1.04-.337l-.306-.459c-.245.184-.49.276-.765.276-.52 0-.918-.398-.918-.98 0-.58.398-.978.918-.978.275 0 .52.091.765.275l.306-.459c-.306-.245-.612-.336-1.04-.336-.918-.062-1.53.58-1.53 1.468zm-4.1-1.53c-.856 0-1.437.612-1.437 1.53s.612 1.53 1.499 1.53c.428 0 .856-.123 1.193-.398l-.306-.428c-.245.183-.55.306-.857.306-.397 0-.826-.245-.887-.765h2.172v-.245c0-.918-.55-1.53-1.376-1.53zm-.03.551c.428 0 .734.275.765.734H25.3c.09-.428.366-.734.825-.734zm-7.923.98v-1.47h-.582v.368c-.214-.276-.52-.429-.948-.429-.826 0-1.438.643-1.438 1.53s.612 1.53 1.438 1.53c.428 0 .734-.153.948-.429v.368h.582v-1.469zm-2.356 0c0-.552.336-.98.918-.98.55 0 .887.428.887.98 0 .58-.367.978-.887.978-.582.03-.918-.428-.918-.979zm22.608-1.53a.915.915 0 0 0-.796.428v-.367h-.58v2.937h.58V28.42c0-.49.245-.796.643-.796.122 0 .275.03.398.061l.183-.55a2.066 2.066 0 0 0-.428-.062zm-2.264 1.53v-1.47h-.581v.368c-.214-.276-.52-.429-.949-.429-.826 0-1.437.643-1.437 1.53s.611 1.53 1.437 1.53c.429 0 .735-.153.949-.429v.368h.581v-1.469zm-2.356 0c0-.552.337-.98.918-.98.55 0 .887.428.887.98 0 .58-.367.978-.887.978-.581.03-.918-.428-.918-.979zm8.26 0v-2.632h-.58v1.53c-.215-.276-.52-.429-.95-.429-.825 0-1.437.643-1.437 1.53s.612 1.53 1.438 1.53c.428 0 .734-.153.948-.429v.368h.581v-1.469zm-2.355 0c0-.552.336-.98.918-.98.55 0 .887.428.887.98 0 .58-.367.978-.887.978-.582.03-.918-.428-.918-.979z"/>
              <path fill="#FF5F00" d="M20.711 4.864h10.616V22.18H20.71z"/>
              <path fill="#EB001B" d="M21.782 13.522c0-3.518 1.652-6.639 4.191-8.658a10.946 10.946 0 0 0-6.791-2.355A11.006 11.006 0 0 0 8.168 13.522a11.006 11.006 0 0 0 11.014 11.013c2.57 0 4.925-.887 6.791-2.355-2.54-2.02-4.191-5.14-4.191-8.658z"/>
              <path fill="#F79E1B" d="M43.809 13.522a11.006 11.006 0 0 1-11.014 11.013c-2.57 0-4.925-.887-6.791-2.355a10.95 10.95 0 0 0 4.19-8.658c0-3.518-1.651-6.639-4.19-8.658a10.946 10.946 0 0 1 6.791-2.355A11.006 11.006 0 0 1 43.81 13.522z"/>
            </g>
          </svg>
        </span>
      );
    }
    else {
      return (
        <span className={'spc-card-type ' + this.props.class} />
      );
    }
  }
}

export default CardTypeSVG;
