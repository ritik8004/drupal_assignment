import React from 'react';

const CardTypeSVG = (props) => {
  const { type, class: classValue } = props;
  if (type === 'visa') {
    return (
      <span className={`spc-card-type ${classValue}`}>
        <svg xmlns="http://www.w3.org/2000/svg" width="52" height="33" viewBox="0 0 52 33">
          <g fill="none" fillRule="evenodd">
            <path fill="#FFF" d="M0 33h52V0H0z" />
            <path fill="#F7B600" d="M0 33h52v-5H0z" />
            <path fill="#1A1F71" d="M0 5h52V0H0zM25.566 10.27l-2.65 12.304h-3.204l2.65-12.305h3.204zm13.482 7.944l1.688-4.62.97 4.62h-2.658zm3.578 4.36h2.963L43 10.269h-2.734c-.616 0-1.135.355-1.365.902l-4.808 11.403h3.365l.668-1.838h4.111l.389 1.838zm-8.367-4.018c.015-3.247-4.519-3.427-4.488-4.878.01-.44.433-.91 1.358-1.03.46-.06 1.725-.106 3.161.55l.561-2.61a8.691 8.691 0 0 0-2.997-.544c-3.168 0-5.397 1.672-5.415 4.067-.02 1.772 1.592 2.76 2.804 3.35 1.25.603 1.67.99 1.663 1.528-.009.826-.997 1.191-1.916 1.205-1.613.025-2.547-.433-3.292-.778l-.581 2.698c.75.34 2.131.637 3.562.653 3.368 0 5.57-1.652 5.58-4.21zM20.987 10.27l-5.193 12.305h-3.387l-2.555-9.82c-.155-.604-.29-.826-.761-1.081-.77-.416-2.043-.805-3.162-1.047l.076-.357h5.453c.695 0 1.32.46 1.479 1.254l1.35 7.12 3.333-8.374h3.367z" />
          </g>
        </svg>
      </span>
    );
  }
  if (type === 'mastercard') {
    return (
      <span className={`spc-card-type ${classValue}`}>
        <svg className="mastercard" xmlns="http://www.w3.org/2000/svg" width="52" height="33" viewBox="0 0 52 33">
          <g fill="none" fillRule="nonzero">
            <path fill="#000" d="M0 0h52v33H0z" />
            <path fill="#FFF" d="M14.623 30.073v-1.836c0-.704-.428-1.163-1.132-1.163-.367 0-.764.123-1.04.52-.214-.336-.52-.52-.979-.52-.306 0-.642.092-.887.429v-.367h-.581v2.937h.581V28.39c0-.52.306-.765.734-.765.429 0 .673.275.673.765v1.683h.582V28.39c0-.52.305-.765.734-.765.428 0 .673.275.673.765v1.683h.642zm9.545-2.907h-1.07v-.887h-.582v.887h-.612v.52h.612v1.377c0 .673.245 1.07.98 1.07.274 0 .58-.091.795-.213l-.184-.52a1.007 1.007 0 0 1-.55.152c-.307 0-.46-.183-.46-.489v-1.407h1.071v-.49zm5.446-.092a.915.915 0 0 0-.796.429v-.367h-.581v2.937h.581V28.42c0-.49.245-.796.643-.796.122 0 .275.03.397.061l.184-.55a2.066 2.066 0 0 0-.428-.062zm-8.23.306c-.306-.214-.734-.306-1.193-.306-.734 0-1.193.337-1.193.918 0 .49.337.765.979.857l.306.03c.336.062.55.184.55.337 0 .214-.244.367-.703.367-.459 0-.765-.153-.979-.306l-.306.459c.428.306.948.367 1.254.367.857 0 1.316-.398 1.316-.948 0-.52-.367-.765-1.01-.857l-.306-.03c-.275-.031-.52-.123-.52-.306 0-.215.245-.367.582-.367.367 0 .734.152.917.244l.306-.459zm8.872 1.224c0 .887.581 1.53 1.53 1.53.428 0 .734-.092 1.04-.337l-.306-.459c-.245.184-.49.276-.765.276-.52 0-.918-.398-.918-.98 0-.58.398-.978.918-.978.275 0 .52.091.765.275l.306-.459c-.306-.245-.612-.336-1.04-.336-.918-.062-1.53.58-1.53 1.468zm-4.1-1.53c-.856 0-1.437.612-1.437 1.53s.612 1.53 1.499 1.53c.428 0 .856-.123 1.193-.398l-.306-.428c-.245.183-.55.306-.857.306-.397 0-.826-.245-.887-.765h2.172v-.245c0-.918-.55-1.53-1.376-1.53zm-.03.551c.428 0 .734.275.765.734H25.3c.09-.428.366-.734.825-.734zm-7.923.98v-1.47h-.582v.368c-.214-.276-.52-.429-.948-.429-.826 0-1.438.643-1.438 1.53s.612 1.53 1.438 1.53c.428 0 .734-.153.948-.429v.368h.582v-1.469zm-2.356 0c0-.552.336-.98.918-.98.55 0 .887.428.887.98 0 .58-.367.978-.887.978-.582.03-.918-.428-.918-.979zm22.608-1.53a.915.915 0 0 0-.796.428v-.367h-.58v2.937h.58V28.42c0-.49.245-.796.643-.796.122 0 .275.03.398.061l.183-.55a2.066 2.066 0 0 0-.428-.062zm-2.264 1.53v-1.47h-.581v.368c-.214-.276-.52-.429-.949-.429-.826 0-1.437.643-1.437 1.53s.611 1.53 1.437 1.53c.429 0 .735-.153.949-.429v.368h.581v-1.469zm-2.356 0c0-.552.337-.98.918-.98.55 0 .887.428.887.98 0 .58-.367.978-.887.978-.581.03-.918-.428-.918-.979zm8.26 0v-2.632h-.58v1.53c-.215-.276-.52-.429-.95-.429-.825 0-1.437.643-1.437 1.53s.612 1.53 1.438 1.53c.428 0 .734-.153.948-.429v.368h.581v-1.469zm-2.355 0c0-.552.336-.98.918-.98.55 0 .887.428.887.98 0 .58-.367.978-.887.978-.582.03-.918-.428-.918-.979z" />
            <path fill="#FF5F00" d="M20.711 4.864h10.616V22.18H20.71z" />
            <path fill="#EB001B" d="M21.782 13.522c0-3.518 1.652-6.639 4.191-8.658a10.946 10.946 0 0 0-6.791-2.355A11.006 11.006 0 0 0 8.168 13.522a11.006 11.006 0 0 0 11.014 11.013c2.57 0 4.925-.887 6.791-2.355-2.54-2.02-4.191-5.14-4.191-8.658z" />
            <path fill="#F79E1B" d="M43.809 13.522a11.006 11.006 0 0 1-11.014 11.013c-2.57 0-4.925-.887-6.791-2.355a10.95 10.95 0 0 0 4.19-8.658c0-3.518-1.651-6.639-4.19-8.658a10.946 10.946 0 0 1 6.791-2.355A11.006 11.006 0 0 1 43.81 13.522z" />
          </g>
        </svg>
      </span>
    );
  }
  if (type === 'diners') {
    return (
      <span className={`spc-card-type ${classValue}`}>
        <svg xmlns="http://www.w3.org/2000/svg" width="52" height="33" viewBox="0 0 52 33">
          <g fill="none" fillRule="evenodd">
            <rect width="52" height="33" fill="#FFF" rx="3" />
            <rect width="51.5" height="32.5" x=".25" y=".25" stroke="#065388" strokeWidth=".5" rx="3" />
            <g fill="#231F20">
              <path d="M7.318 19.955c0-.724-.378-.676-.74-.684v-.21c.314.016.636.016.95.016.338 0 .797-.015 1.393-.015 2.085 0 3.22 1.392 3.22 2.818 0 .797-.467 2.802-3.317 2.802-.41 0-.789-.016-1.167-.016-.362 0-.717.007-1.079.016v-.21c.483-.049.717-.064.74-.612v-3.905zm.79 3.776c0 .62.443.692.837.692 1.74 0 2.31-1.312 2.31-2.511 0-1.506-.966-2.592-2.52-2.592-.33 0-.482.023-.628.032v4.38zM12.424 24.472h.152c.226 0 .387 0 .387-.266v-2.182c0-.354-.12-.402-.42-.563v-.129c.38-.113.83-.265.862-.29a.285.285 0 0 1 .146-.04c.039 0 .056.048.056.113v3.09c0 .267.177.267.403.267h.136v.21c-.274 0-.556-.016-.845-.016-.29 0-.58.007-.877.016v-.21zm.86-4.71a.413.413 0 0 1-.394-.402c0-.202.194-.387.395-.387.209 0 .395.17.395.387a.402.402 0 0 1-.395.402zM14.911 22.073c0-.298-.089-.378-.466-.531v-.153c.345-.113.675-.218 1.062-.387.024 0 .048.016.048.08v.524c.46-.33.854-.604 1.394-.604.683 0 .925.5.925 1.127v2.077c0 .266.177.266.403.266h.145v.21c-.283 0-.564-.016-.854-.016-.29 0-.58.007-.87.016v-.21h.146c.225 0 .385 0 .385-.266v-2.084c0-.46-.28-.685-.74-.685-.257 0-.668.209-.934.387v2.382c0 .266.178.266.403.266h.145v.21c-.282 0-.564-.016-.854-.016-.29 0-.58.007-.87.016v-.21h.146c.225 0 .386 0 .386-.266v-2.133zM19.034 22.46c-.017.072-.017.193 0 .467.047.764.54 1.392 1.183 1.392.443 0 .79-.242 1.087-.539l.112.113c-.37.49-.829.91-1.488.91-1.28 0-1.538-1.241-1.538-1.756 0-1.578 1.062-2.045 1.625-2.045.653 0 1.354.41 1.361 1.264 0 .049 0 .097-.007.145l-.073.049h-2.262zm1.425-.258c.201 0 .225-.105.225-.202 0-.41-.25-.74-.7-.74-.491 0-.83.362-.926.942h1.401zM21.562 24.472h.217c.225 0 .386 0 .386-.266v-2.262c0-.25-.298-.298-.418-.363v-.12c.587-.25.91-.46.983-.46.047 0 .071.025.071.106v.725h.017c.201-.314.54-.83 1.03-.83.202 0 .46.136.46.427 0 .217-.153.411-.378.411-.25 0-.25-.194-.533-.194-.137 0-.587.186-.587.669v1.89c0 .267.16.267.386.267h.45v.21c-.442-.009-.78-.016-1.126-.016-.33 0-.668.007-.958.016v-.21zM24.661 23.562c.105.531.427.983 1.015.983.475 0 .652-.29.652-.572 0-.95-1.755-.644-1.755-1.94 0-.452.363-1.03 1.248-1.03.258 0 .604.072.917.233l.057.82h-.185c-.08-.506-.362-.796-.878-.796-.322 0-.628.185-.628.531 0 .943 1.868.652 1.868 1.916 0 .531-.427 1.096-1.385 1.096-.322 0-.7-.113-.982-.274l-.089-.926.145-.04zM34.242 20.519h-.201c-.153-.942-.821-1.32-1.723-1.32-.926 0-2.27.62-2.27 2.552 0 1.626 1.16 2.794 2.399 2.794.796 0 1.458-.548 1.619-1.394l.185.049-.185 1.175c-.339.21-1.249.428-1.78.428-1.884 0-3.075-1.216-3.075-3.028 0-1.65 1.473-2.835 3.051-2.835.652 0 1.28.21 1.9.428l.08 1.15zM34.533 24.472h.152c.226 0 .387 0 .387-.266v-4.484c0-.523-.12-.54-.427-.628v-.129c.322-.104.66-.249.83-.346.087-.048.152-.09.176-.09.05 0 .065.05.065.115v5.562c0 .266.177.266.403.266h.136v.21c-.273 0-.555-.016-.845-.016-.29 0-.58.007-.877.016v-.21zM39.702 24.238c0 .146.088.153.224.153.098 0 .218-.007.323-.007v.17c-.346.031-1.007.2-1.16.249l-.04-.025v-.652c-.483.394-.853.677-1.426.677-.434 0-.885-.283-.885-.958v-2.062c0-.21-.032-.41-.482-.45v-.154c.29-.008.933-.056 1.038-.056.09 0 .09.056.09.234v2.076c0 .242 0 .934.7.934.273 0 .635-.209.973-.49V21.71c0-.161-.386-.25-.676-.33v-.145c.724-.049 1.175-.113 1.256-.113.065 0 .065.056.065.145v2.97zM41.304 21.581c.322-.273.757-.58 1.2-.58.933 0 1.497.815 1.497 1.692 0 1.055-.773 2.11-1.925 2.11-.595 0-.91-.194-1.12-.283l-.24.186-.169-.089a9.45 9.45 0 0 0 .113-1.433v-3.462c0-.523-.121-.54-.427-.628v-.129c.322-.104.66-.249.829-.346.089-.048.153-.089.178-.089.047 0 .064.05.064.113v2.938zm0 2.19c0 .306.29.822.829.822.861 0 1.223-.845 1.223-1.562 0-.87-.659-1.594-1.287-1.594-.299 0-.548.193-.765.379v1.956zM6.57 29.363h.06c.158 0 .325-.021.325-.25v-2.295c0-.228-.167-.25-.324-.25h-.062v-.131c.17 0 .434.017.649.017.219 0 .481-.017.687-.017v.131h-.061c-.157 0-.324.022-.324.25v2.295c0 .229.167.25.324.25h.061v.132c-.21 0-.473-.017-.691-.017-.216 0-.474.017-.645.017v-.132zM10.915 28.631l.009-.009V26.98c0-.36-.25-.412-.382-.412h-.096v-.131c.206 0 .408.017.613.017.18 0 .36-.017.54-.017v.131h-.066c-.184 0-.39.035-.39.557v1.993c0 .153.004.307.025.443h-.166l-2.256-2.516v1.806c0 .381.074.512.412.512h.074v.132c-.188 0-.376-.017-.565-.017-.196 0-.398.017-.596.017v-.132h.062c.302 0 .394-.206.394-.555v-1.846a.393.393 0 0 0-.398-.394H8.07v-.131c.167 0 .338.017.504.017.132 0 .259-.017.39-.017l1.95 2.194zM12.255 26.656c-.33 0-.342.079-.408.398h-.131c.017-.122.039-.245.052-.372.018-.123.027-.245.027-.372h.105c.035.131.145.127.263.127h2.26c.119 0 .228-.005.237-.136l.105.018c-.017.118-.035.236-.048.354-.008.119-.008.237-.008.355l-.132.049c-.01-.162-.03-.421-.324-.421h-.719v2.33c0 .338.154.377.364.377h.083v.132c-.17 0-.478-.017-.714-.017-.263 0-.57.017-.74.017v-.132h.083c.241 0 .363-.022.363-.368v-2.34h-.718zM14.905 29.363h.061c.158 0 .325-.021.325-.25v-2.295c0-.228-.167-.25-.325-.25h-.061v-.131c.266 0 .723.017 1.09.017.369 0 .824-.017 1.122-.017-.008.188-.004.478.009.67l-.132.035c-.021-.285-.074-.512-.534-.512h-.608v1.147h.52c.263 0 .32-.148.346-.385h.132a10.195 10.195 0 0 0 0 1.012l-.132.026c-.026-.263-.039-.433-.341-.433h-.525v1.02c0 .285.253.285.533.285.526 0 .758-.035.89-.534l.122.03c-.057.233-.11.464-.148.697-.281 0-.785-.017-1.178-.017-.395 0-.916.017-1.166.017v-.132zM17.989 26.9c0-.319-.176-.332-.312-.332h-.079v-.131c.14 0 .412.017.68.017.262 0 .473-.017.705-.017.551 0 1.043.148 1.043.771 0 .394-.263.635-.61.771l.75 1.121c.123.185.21.237.425.263v.132c-.145 0-.285-.017-.43-.017-.135 0-.276.017-.411.017a12.129 12.129 0 0 1-.912-1.42h-.288v.938c0 .337.157.35.358.35h.08v.132c-.251 0-.504-.017-.754-.017-.21 0-.417.017-.636.017v-.132h.08c.162 0 .31-.074.31-.236v-2.226zm.56 1.017h.215c.438 0 .674-.166.674-.683 0-.39-.25-.64-.64-.64-.131 0-.187.014-.248.018v1.305zM23.565 28.631l.008-.009V26.98c0-.36-.25-.412-.38-.412h-.097v-.131c.206 0 .407.017.613.017.18 0 .359-.017.54-.017v.131h-.066c-.184 0-.39.035-.39.557v1.993c0 .153.004.307.026.443h-.166l-2.256-2.516v1.806c0 .381.074.512.411.512h.075v.132c-.188 0-.377-.017-.565-.017-.198 0-.4.017-.596.017v-.132h.06c.303 0 .395-.206.395-.555v-1.846a.393.393 0 0 0-.398-.394h-.057v-.131c.165 0 .338.017.504.017.13 0 .258-.017.39-.017l1.949 2.194zM24.953 28.868c-.044.149-.097.264-.097.342 0 .132.184.153.329.153h.049v.132a9.949 9.949 0 0 0-.531-.017c-.158 0-.315.007-.473.017v-.132h.026c.17 0 .316-.1.381-.285l.701-2.01c.057-.163.136-.381.163-.544a2.23 2.23 0 0 0 .398-.188c.014-.005.022-.01.035-.01.013 0 .021 0 .031.015.013.034.026.074.04.109l.806 2.291c.052.153.104.315.16.447.054.123.146.175.29.175h.027v.132a12.458 12.458 0 0 0-1.262 0v-.132h.048c.1 0 .272-.017.272-.127 0-.056-.04-.175-.088-.316l-.17-.508h-.995l-.14.456zm.64-1.94h-.01l-.407 1.24h.819l-.402-1.24zM27.48 26.656c-.328 0-.341.079-.407.398h-.132c.018-.122.04-.245.053-.372.018-.123.026-.245.026-.372h.106c.034.131.144.127.262.127h2.261c.118 0 .227-.005.236-.136l.105.018c-.016.118-.034.236-.048.354-.01.119-.01.237-.01.355l-.13.049c-.008-.162-.03-.421-.324-.421h-.718v2.33c0 .338.153.377.363.377h.084v.132c-.171 0-.478-.017-.715-.017-.262 0-.57.017-.74.017v-.132h.083c.242 0 .364-.022.364-.368v-2.34h-.718zM30.152 29.363h.061c.158 0 .324-.021.324-.25v-2.295c0-.228-.166-.25-.324-.25h-.061v-.131c.171 0 .433.017.648.017.22 0 .482-.017.689-.017v.131h-.062c-.158 0-.325.022-.325.25v2.295c0 .229.167.25.325.25h.062v.132c-.21 0-.474-.017-.693-.017-.215 0-.473.017-.644.017v-.132zM33.235 26.371c.935 0 1.679.579 1.679 1.512 0 1.008-.723 1.677-1.656 1.677-.93 0-1.638-.63-1.638-1.572 0-.911.705-1.617 1.615-1.617m.067 2.997c.85 0 .998-.75.998-1.389 0-.64-.345-1.415-1.073-1.415-.766 0-.994.683-.994 1.27 0 .784.36 1.534 1.069 1.534M37.866 28.631l.01-.009V26.98c0-.36-.251-.412-.383-.412h-.095v-.131c.205 0 .407.017.612.017.18 0 .36-.017.54-.017v.131h-.066c-.184 0-.39.035-.39.557v1.993c0 .153.004.307.026.443h-.166l-2.257-2.516v1.806c0 .381.075.512.412.512h.075v.132c-.189 0-.377-.017-.565-.017-.198 0-.4.017-.596.017v-.132h.061c.303 0 .394-.206.394-.555v-1.846c0-.245-.2-.394-.399-.394h-.056v-.131c.166 0 .337.017.504.017.13 0 .257-.017.389-.017l1.95 2.194zM39.254 28.868c-.043.149-.096.264-.096.342 0 .132.184.153.328.153h.049v.132a7.868 7.868 0 0 0-1.003 0v-.132h.025c.171 0 .317-.1.38-.285l.703-2.01c.057-.163.136-.381.161-.544.14-.048.316-.135.4-.188.012-.005.021-.01.035-.01.012 0 .02 0 .03.015.013.034.026.074.04.109l.805 2.291c.053.153.106.315.163.447.052.123.144.175.289.175h.027v.132a12.457 12.457 0 0 0-1.263 0v-.132h.049c.1 0 .272-.017.272-.127 0-.056-.04-.175-.089-.316l-.17-.508h-.995l-.14.456zm.64-1.94h-.009l-.408 1.24h.82l-.403-1.24zM44.055 29.513c-.386 0-.772-.017-1.156-.017-.386 0-.771.017-1.158.017h-.018V29.344h.08c.158-.004.309-.015.31-.274v-2.253c-.001-.213-.151-.227-.31-.23h-.08V26.418h.018c.234 0 .462.017.693.017.223 0 .44-.018.666-.018h.018V26.587h-.128c-.17.005-.275-.003-.279.218v2.26c.001.164.109.208.245.227a3.55 3.55 0 0 0 .575-.009.613.613 0 0 0 .408-.228.987.987 0 0 0 .132-.31l.004-.014H44.24l-.004.023c-.05.25-.11.495-.163.745l-.003.014h-.015zm-.016-.037c.05-.238.108-.47.155-.707h-.09a.992.992 0 0 1-.135.308.65.65 0 0 1-.434.244 3.412 3.412 0 0 1-.582.008c-.143-.015-.28-.076-.28-.264v-2.26c0-.244.154-.256.317-.256h.09v-.093c-.215 0-.428.017-.646.017-.227 0-.449-.016-.673-.017v.093h.042c.156 0 .347.027.347.268v2.253c0 .283-.19.313-.347.313h-.042v.093c.378-.001.757-.018 1.138-.018.38 0 .76.017 1.14.018zM44.164 26.312c.264 0 .461.203.461.463s-.197.46-.461.46a.452.452 0 0 1-.462-.46c0-.26.198-.463.462-.463zm0 .837a.372.372 0 0 0 .365-.374.372.372 0 0 0-.365-.376.373.373 0 0 0-.366.376c0 .198.158.374.366.374zm-.23-.131v-.022c.057-.009.067-.007.067-.042v-.341c0-.048-.005-.065-.065-.062v-.024h.236c.081 0 .156.039.156.123 0 .068-.045.12-.109.139l.076.106a.49.49 0 0 0 .101.108v.015h-.09c-.042 0-.08-.09-.165-.212h-.051v.153c0 .03.01.028.067.037v.022h-.223zm.156-.243h.054c.06 0 .087-.045.087-.118s-.042-.1-.09-.1h-.051v.218z" />
              <path d="M7.905 29.513c-.212 0-.474-.017-.692-.017-.214 0-.472.017-.644.017H6.55V29.344h.08c.16-.004.304-.017.305-.23v-2.296c-.001-.214-.146-.228-.305-.23h-.08V26.417h.02c.171 0 .434.017.648.017.218 0 .48-.018.687-.018h.02V26.587h-.08c-.16.003-.305.017-.306.231v2.295c0 .214.146.227.305.231h.081V29.514h-.02zm-.019-.037v-.093h-.042c-.156 0-.343-.028-.344-.27v-2.295c.001-.242.188-.269.344-.269h.042v-.093c-.202 0-.456.017-.668.017-.208 0-.46-.016-.63-.017v.093h.042c.157 0 .343.027.343.27v2.294c0 .242-.186.27-.343.27h-.042v.093c.17-.001.417-.018.625-.018.214 0 .467.017.673.018zM11.168 29.579l-.18-.006-2.223-2.478v1.756c.005.38.063.489.393.493h.094V29.513h-.02c-.19 0-.378-.017-.565-.017-.196 0-.397.017-.596.017h-.019V29.345h.08c.29-.003.373-.189.376-.537v-1.846a.375.375 0 0 0-.38-.375h-.076V26.417h.02c.167 0 .338.018.502.018.131 0 .258-.017.405-.01l1.926 2.167V26.98c-.002-.348-.235-.39-.362-.393h-.116V26.417h.019c.207 0 .409.018.613.018.179 0 .358-.017.54-.017h.018V26.587h-.084c-.18.005-.366.02-.372.538v1.993c0 .153.005.306.026.44l.004.02h-.023zm-.166-.038h.145a2.95 2.95 0 0 1-.023-.423v-1.993c0-.526.22-.576.409-.576h.046v-.093c-.172 0-.345.017-.52.017-.2 0-.395-.016-.593-.017v.093h.077c.134 0 .4.06.4.431l-.006 1.656-.009.009-.015.014-1.948-2.203c-.13 0-.257.017-.39.017-.161 0-.326-.016-.484-.017l-.001.093h.039c.205 0 .417.157.417.413v1.846c0 .35-.098.574-.413.575l-.042-.001v.094c.19-.002.385-.018.576-.018.183 0 .365.016.546.018v-.093h-.055c-.345-.001-.43-.151-.43-.532v-1.855l2.274 2.545zm-.087-.91l.014-.012-.014.012zm-.01-.009v-.001l-.004-.003.004.004zM13.981 29.513c-.172 0-.478-.017-.714-.017-.263 0-.569.017-.74.017h-.02V29.344H12.61c.241-.006.338-.009.344-.349v-2.32h-.7v-.039h.738v2.36c0 .351-.142.387-.382.387h-.065v.093c.173-.001.468-.018.722-.018.228 0 .52.017.695.018v-.093h-.064c-.212 0-.383-.052-.383-.397v-2.35h.738c.294.001.332.251.342.414l.094-.035c0-.114 0-.229.009-.344.013-.113.029-.224.045-.337l-.067-.01c-.02.126-.143.133-.253.132H12.14c-.103 0-.215-.005-.254-.127h-.073c0 .12-.009.238-.025.355-.013.12-.033.236-.05.352h.094c.058-.305.095-.404.423-.4v.039c-.325.006-.318.06-.39.384l-.002.015H11.694l.002-.023c.018-.122.04-.245.053-.372.018-.122.027-.244.027-.369v-.02H11.915l.003.014c.03.11.11.112.222.113h2.283c.122-.001.21-.003.218-.118l.002-.02.02.004.123.02-.003.018a9.953 9.953 0 0 0-.047.354c-.009.117-.009.235-.009.353v.013l-.012.005-.155.057-.001-.025c-.012-.165-.03-.403-.306-.403h-.7v2.311c.005.33.136.354.345.358H14V29.514h-.019zM17.248 29.513c-.28 0-.785-.018-1.178-.018-.394 0-.915.018-1.165.018h-.019V29.344h.08c.159-.002.303-.017.305-.23v-2.296c-.002-.214-.146-.228-.305-.23h-.08V26.417h.019c.267 0 .723.017 1.09.017.368 0 .823-.018 1.122-.018h.019v.02a6.907 6.907 0 0 0 .009.668v.016l-.015.005-.154.04v-.023c-.027-.284-.065-.49-.516-.495h-.59v1.11h.502c.253-.003.298-.132.328-.368v-.018H16.87v.02a9.832 9.832 0 0 0 0 1.01v.017l-.016.003-.152.03-.001-.02c-.03-.267-.033-.414-.323-.417h-.507v1.002c0 .266.23.265.515.266.528-.003.74-.029.871-.52l.005-.018.018.003.14.036-.003.018c-.057.232-.11.464-.15.696l-.002.015h-.016zm-.016-.037c.038-.222.088-.443.142-.664l-.086-.021c-.131.49-.385.533-.903.53-.276 0-.552 0-.553-.304v-1.04h.545c.306-.003.336.182.358.43l.095-.018a9.286 9.286 0 0 1 0-.979h-.095c-.024.23-.096.389-.363.386h-.54V26.61h.628c.456-.003.531.233.551.508l.094-.026a7.375 7.375 0 0 1-.008-.636c-.297 0-.742.017-1.102.017-.36 0-.803-.016-1.071-.017v.093h.042c.156 0 .343.027.344.27v2.294c-.001.242-.188.269-.344.27h-.042v.093c.255-.001.761-.018 1.146-.018.387 0 .88.017 1.162.018zM20.59 29.513c-.146 0-.286-.017-.43-.017-.133 0-.273.017-.426.01a12.249 12.249 0 0 1-.907-1.411h-.259v.917c.005.33.137.327.34.332h.098V29.514h-.02c-.25 0-.504-.018-.753-.018-.209 0-.415.017-.635.017h-.019V29.344h.098c.158 0 .291-.07.292-.217v-2.226c-.004-.311-.156-.31-.292-.314h-.098V26.418h.02c.141 0 .412.017.679.017.261 0 .471-.018.705-.018.552.002 1.06.154 1.062.79 0 .396-.262.643-.6.78l.736 1.102c.123.182.2.228.412.255l.016.003V29.513h-.018zm-2.04-1.457h.299l.005.01c.285.503.573.976.896 1.41.133 0 .274-.018.41-.018.14 0 .275.016.411.018v-.097c-.205-.025-.301-.087-.42-.268l-.764-1.14.022-.01c.343-.135.597-.369.597-.754 0-.607-.472-.749-1.023-.751-.23 0-.441.017-.705.017-.258 0-.518-.016-.661-.017v.093h.06c.136 0 .33.023.33.352v2.226c0 .176-.165.256-.33.256h-.06v.093c.21-.001.41-.018.616-.018.245 0 .492.017.735.018v-.093h-.06c-.2 0-.378-.025-.378-.37v-.957h.02zm0-.12h-.02v-1.342h.017c.06-.006.119-.018.251-.018.4 0 .66.26.66.659-.002.523-.252.7-.694.7h-.215zm.214-.037c.433-.005.652-.155.656-.664-.003-.383-.24-.62-.622-.622-.118 0-.174.01-.23.016v1.27h.196zM23.819 29.579l-.18-.006-2.224-2.478v1.755c.005.382.063.49.393.494h.093V29.513h-.02c-.189 0-.377-.018-.564-.018-.196 0-.398.018-.596.018h-.02V29.345h.081c.29-.002.373-.189.376-.538v-1.845a.374.374 0 0 0-.38-.375h-.076V26.418h.02c.167 0 .338.017.504.017.128 0 .255-.017.402-.012l1.926 2.169V26.98c-.001-.348-.235-.39-.362-.393h-.115V26.418h.019c.207 0 .409.017.613.017.179 0 .358-.017.539-.017h.02V26.588h-.085c-.181.004-.367.02-.372.537v1.993c0 .153.004.305.026.44l.003.02h-.021zm-.166-.038h.143a3.018 3.018 0 0 1-.023-.423v-1.993c0-.526.222-.575.41-.576h.046v-.093c-.173 0-.346.017-.52.017-.201 0-.396-.016-.594-.017v.093h.077c.135 0 .4.06.4.431l-.006 1.656-.008.009-.014.014-1.949-2.203c-.13 0-.257.017-.39.017-.162 0-.326-.016-.485-.017v.093h.039a.412.412 0 0 1 .417.413v1.845c0 .351-.1.575-.414.576h-.042v.093c.19-.002.385-.018.577-.018.183 0 .366.016.547.018v-.093h-.056c-.345 0-.43-.15-.432-.533v-1.853l2.277 2.544zm-.089-.91l.015-.012-.015.012zm-.01-.009l-.003-.004.003.004zM27.287 29.513c-.197-.008-.395-.018-.603-.018-.215 0-.433.01-.657.018l-.02.002v-.172h.067c.102 0 .252-.023.253-.107 0-.05-.039-.17-.087-.31l-.167-.495h-.966l-.136.443c-.044.149-.098.265-.097.336.002.107.165.133.31.133h.068v.172l-.02-.002c-.175-.008-.354-.018-.529-.018-.157 0-.315.01-.472.018l-.02.002v-.172h.045c.163 0 .3-.093.362-.27l.702-2.012c.057-.162.136-.38.174-.555.137-.047.314-.135.398-.188.012-.004.024-.01.042-.01.01 0 .034.002.049.025l.039.11.807 2.292c.051.153.104.315.16.445.052.117.132.163.273.163h.044v.172l-.02-.002zm-1.242-.038c.217-.008.43-.017.639-.017.203 0 .394.009.585.017v-.092h-.007c-.15 0-.254-.058-.307-.188-.057-.132-.11-.294-.162-.448l-.807-2.291-.037-.106c-.004-.004-.003-.004-.007-.004h-.008c-.009 0-.014.003-.026.006a1.62 1.62 0 0 1-.39.175c-.026.166-.105.385-.162.547l-.701 2.01a.417.417 0 0 1-.399.299h-.007v.092c.15-.008.302-.017.454-.017.17 0 .342.009.51.017v-.092h-.028c-.143-.002-.343-.018-.348-.173 0-.087.054-.199.097-.347l.019.005-.019-.006.144-.468H26.1l.175.52c.05.141.09.258.09.322-.007.134-.191.144-.29.147h-.03v.092zm-.895-1.289l.419-1.278h.023v.02h-.004.004v-.02h.014l.416 1.278h-.872zm.052-.038h.767l-.381-1.174-.386 1.174zm.371-1.215l.01-.003-.01.003zM29.207 29.513c-.172 0-.48-.017-.715-.017-.262 0-.568.017-.74.017h-.02V29.344H27.836c.242-.006.339-.009.345-.349v-2.32h-.7v-.039h.738v2.36c0 .351-.142.386-.383.387h-.064v.093c.173-.001.467-.018.721-.018.229 0 .522.017.695.018v-.093h-.064c-.211-.001-.382-.052-.382-.397v-2.35h.737c.294.001.333.251.341.414l.095-.035c0-.114.001-.229.01-.344.012-.112.028-.224.044-.336l-.066-.012c-.02.127-.144.135-.253.133h-2.283c-.103 0-.215-.005-.255-.128h-.071c-.001.121-.01.24-.027.356-.012.12-.034.237-.05.352h.094c.058-.305.095-.405.424-.4v.039c-.325.005-.318.06-.39.384l-.003.015H26.92l.003-.022c.018-.123.04-.246.053-.372.017-.123.026-.245.026-.37v-.02H27.14l.003.014c.031.11.11.112.223.113h2.283c.121-.001.21-.003.218-.117v-.021l.02.004.125.02-.003.018a8.622 8.622 0 0 0-.048.354c-.009.117-.009.235-.009.353v.013l-.013.005-.155.057v-.025c-.012-.165-.029-.403-.306-.403h-.7v2.311c.005.33.136.354.345.358h.102V29.514h-.018zM31.489 29.513c-.212 0-.474-.018-.693-.018-.214 0-.473.018-.644.018h-.019V29.344h.081c.157-.002.303-.016.304-.23v-2.296c0-.214-.147-.228-.304-.23h-.08V26.417h.018c.171 0 .435.017.648.017.218 0 .481-.018.689-.018h.018V26.587h-.08c-.16.003-.304.017-.306.231v2.295c.002.215.146.229.306.231h.08V29.514h-.018zm-.02-.037v-.093h-.042c-.157 0-.343-.028-.343-.27v-2.295c0-.242.186-.269.343-.27h.042v-.092c-.202 0-.456.017-.67.017-.207 0-.458-.016-.628-.017v.093h.043c.155 0 .342.027.342.27v2.294c0 .242-.187.27-.342.27h-.043v.093c.169-.002.417-.018.625-.018.213 0 .466.017.673.018zM31.6 27.988a1.615 1.615 0 0 1 1.635-1.636v.039c-.9 0-1.597.695-1.598 1.597.002.93.7 1.552 1.621 1.553.923 0 1.636-.66 1.637-1.659 0-.921-.733-1.491-1.66-1.492v-.038c.94.001 1.696.586 1.698 1.53-.001 1.019-.733 1.695-1.675 1.697-.937-.002-1.656-.64-1.658-1.591m.613-.154c.002-.59.232-1.29 1.014-1.29.744.002 1.09.791 1.092 1.435-.001.639-.152 1.407-1.017 1.407v-.038c.833 0 .977-.73.979-1.369 0-.635-.342-1.395-1.054-1.396-.752 0-.974.667-.976 1.25 0 .781.357 1.514 1.05 1.515v.038c-.726-.001-1.086-.764-1.088-1.552M38.12 29.579l-.18-.006-2.224-2.478v1.756c.005.381.063.488.392.492h.095V29.513h-.02c-.189 0-.377-.017-.564-.017-.197 0-.398.017-.597.017h-.018V29.343h.08c.289 0 .372-.187.375-.535v-1.846a.374.374 0 0 0-.38-.375h-.075V26.418h.018c.168 0 .339.017.504.017.13 0 .256-.017.405-.01l1.925 2.167V26.98c-.002-.348-.235-.39-.363-.393h-.115V26.418h.02c.206 0 .408.017.612.017.179 0 .358-.017.54-.017h.018V26.587h-.084c-.18.005-.367.021-.37.538v1.993c0 .153.003.306.024.44l.004.02h-.021zm-.166-.038h.143a3.103 3.103 0 0 1-.022-.423v-1.993c0-.527.22-.575.409-.576h.047v-.093c-.174 0-.346.017-.521.017-.2 0-.396-.016-.594-.017v.093h.077c.135 0 .4.06.4.431l-.005 1.656-.008.009-.014.014-1.95-2.203c-.13 0-.257.017-.39.017-.161 0-.326-.016-.485-.017v.093h.038a.413.413 0 0 1 .418.413v1.846c0 .35-.098.574-.413.575h-.043v.093c.19-.002.385-.018.578-.018.183 0 .364.016.546.018v-.093h-.057c-.344 0-.43-.15-.43-.532v-1.855l2.276 2.545zm-.088-.91l.014-.012-.014.012zm-.01-.009l-.004-.004.004.004zM41.588 29.513c-.198-.007-.394-.017-.603-.017-.214 0-.434.01-.657.017l-.02.002V29.344h.067c.102 0 .253-.024.253-.108.002-.05-.038-.17-.087-.31l-.166-.495h-.968l-.135.443c-.044.15-.096.265-.095.336 0 .107.164.134.309.134h.067V29.514h-.02c-.175-.008-.354-.018-.529-.018-.157 0-.314.01-.472.017l-.02.002V29.344h.045c.163 0 .3-.094.364-.272l.7-2.01c.057-.163.136-.381.174-.555.138-.048.314-.136.4-.189.01-.004.023-.01.04-.01a.05.05 0 0 1 .048.026c.014.035.027.074.04.11l.806 2.291c.053.153.105.315.162.446.052.116.131.162.271.163h.045V29.514h-.02zm-1.242-.038c.217-.008.43-.017.639-.017.203 0 .394.009.585.017v-.092h-.008c-.148 0-.252-.058-.306-.188-.058-.132-.11-.294-.162-.447l-.807-2.292a3.883 3.883 0 0 1-.037-.105c-.003-.005-.003-.005-.006-.005h-.008c-.01 0-.015.003-.025.006a1.679 1.679 0 0 1-.39.175c-.028.166-.107.384-.164.547l-.701 2.01a.416.416 0 0 1-.4.299h-.006v.092c.151-.008.302-.017.454-.017.17 0 .343.009.511.017v-.092h-.03c-.142-.002-.341-.017-.347-.173.001-.087.055-.199.098-.347l.018.005-.018-.006.144-.468h1.021l.176.52c.048.141.088.258.088.322-.006.134-.19.144-.29.147h-.03v.092zm-.895-1.289l.42-1.278h.024v.02h-.006.005v-.02h.013l.415 1.278h-.871zm.053-.038h.766l-.381-1.173-.385 1.173zm.372-1.215l.009-.003-.01.003zM42.693 29.065c0 .176.122.228.263.246.179.013.376.013.578-.009a.624.624 0 0 0 .42-.237 1 1 0 0 0 .136-.315h.127c-.048.25-.11.495-.162.745-.385 0-.771-.017-1.156-.017-.386 0-.771.017-1.157.017v-.132h.06c.159 0 .33-.021.33-.293v-2.252c0-.228-.171-.25-.33-.25h-.06v-.131c.232 0 .46.017.692.017.224 0 .442-.017.666-.017v.131h-.11c-.166 0-.297.005-.297.237v2.26z" />
            </g>
            <path fill="#FEFEFE" d="M16.8 10.97a7.42 7.42 0 1 1 14.84 0 7.42 7.42 0 0 1-14.84 0" />
            <path fill="#065388" d="M28.707 10.824a4.464 4.464 0 0 0-2.864-4.162v8.324a4.464 4.464 0 0 0 2.864-4.162zm-6.056 4.16V6.664a4.468 4.468 0 0 0-2.863 4.161 4.466 4.466 0 0 0 2.863 4.16zm1.597-11.196a7.036 7.036 0 0 0 0 14.07 7.036 7.036 0 0 0 0-14.07zm-.017 14.734c-4.252.02-7.752-3.424-7.752-7.618 0-4.582 3.5-7.752 7.752-7.752h1.992c4.202 0 8.037 3.168 8.037 7.752 0 4.192-3.835 7.618-8.037 7.618h-1.992z" />
          </g>
        </svg>
      </span>
    );
  }
  if (type === 'mada') {
    return (
      <span className={`spc-card-type ${classValue}`}>
        <svg xmlns="http://www.w3.org/2000/svg" width="98" height="33" viewBox="0 0 250 84" version="1.1" preserveAspectRatio="xMidYMid meet">
          <g fill="none" fillRule="evenodd">
            <g>
              <path d="M230.381439,8.30473124 C230.468505,9.39263718 230.598684,10.3128499 230.605403,11.2341825 C230.630599,14.7386618 230.647956,18.243981 230.592805,21.7481803 C230.579087,22.6104423 230.887598,22.9276316 231.677072,22.9805431 C233.06033,23.0729283 234.440789,23.2546193 235.824608,23.281495 C238.819569,23.3397256 240.778975,21.9085946 241.587486,19.0337346 C242.325448,16.4105543 242.308651,13.7462206 241.611842,11.1149216 C241.195269,9.5418533 240.338606,8.1731523 238.731943,7.65271557 C235.832447,6.7137458 233.013858,7.25293953 230.381439,8.30473124 L230.381439,8.30473124 Z M249.993421,17.7940929 C249.824048,18.493701 249.663634,19.1958287 249.484462,19.8931971 C247.804451,26.4318309 242.43463,30.3842385 235.405235,30.2053471 C232.726344,30.1370381 230.105963,29.737262 227.530095,28.8926372 C226.661114,28.6076428 225.571249,28.9903415 224.583007,29.0762878 C223.274216,29.1899496 221.966545,29.4119541 220.658035,29.4147536 C208.360442,29.4410694 196.06229,29.4304311 183.764418,29.4304311 L182.420353,29.4304311 C182.727744,27.399916 183.016657,25.4889418 183.296613,23.6412374 L198.740901,23.6412374 C198.644877,23.7576988 198.722144,23.7078667 198.721305,23.6591545 C198.657475,19.8797592 198.616601,16.0992441 198.492021,12.3218085 C198.468785,11.618841 198.166153,10.9024356 197.890398,10.2344625 C196.899916,7.83216685 195.089446,6.60036394 192.477184,6.59056551 C190.427632,6.58300672 188.37808,6.58804591 186.328527,6.587486 C185.958147,6.587486 185.588046,6.587486 185.102324,6.587486 C185.3736,4.68351064 185.604563,2.93631019 185.894037,1.19918813 C185.929031,0.989221725 186.284854,0.670632699 186.487262,0.674552072 C189.955347,0.73950168 193.466545,0.535974244 196.879759,1.01861702 C201.991741,1.74146137 205.680711,5.82768757 206.571809,11.2736562 C206.900756,13.2828947 206.925672,15.3484043 206.995661,17.3915174 C207.06313,19.3638018 207.010498,21.3402856 207.010498,23.3903975 C207.379479,23.4181131 207.678471,23.4601064 207.977744,23.4603863 C212.424272,23.4645857 216.871081,23.4360302 221.317329,23.4858623 C222.233903,23.4962206 222.582447,23.0953247 222.675392,22.3156495 C222.740622,21.7658175 222.74874,21.2067469 222.74986,20.6515957 C222.756019,16.9346305 222.74762,13.2173852 222.754339,9.50041993 C222.762178,5.17371221 224.529255,2.67035274 228.665873,1.38115901 C230.308651,0.869400896 232.039614,0.638717805 233.730263,0.27981523 L237.274776,0.27981523 C237.539334,0.349524076 237.800532,0.448628219 238.069849,0.484462486 C243.731663,1.24286114 247.864362,4.85456327 249.371081,10.3864782 C249.606243,11.2495801 249.787094,12.1275196 249.993421,12.9987402 L249.993421,17.7940929 Z" fill="#231F20" />
              <path d="M179.508623,0.78737402 C179.21047,2.7381019 178.922676,4.62136058 178.614166,6.64039754 L177.367805,6.64039754 C172.155879,6.64039754 166.943953,6.63675812 161.732027,6.64459686 C161.003303,6.64571669 160.268701,6.65523516 159.547536,6.74538074 C157.416797,7.01049832 156.287458,8.34056551 156.385442,10.4057951 C156.482307,12.4483483 157.757503,13.8038914 159.816853,13.8610022 C162.455711,13.9337906 165.098208,13.8568029 167.737626,13.9141937 C168.911478,13.9393897 170.104927,14.0275756 171.249664,14.2728163 C175.619205,15.2092665 178.393561,18.7042273 178.329171,23.1795913 C178.312934,24.3162094 178.193673,25.4785834 177.920437,26.5790873 C177.032699,30.156355 174.416237,31.8803191 171.0271,32.5471725 C169.301456,32.8864782 167.514502,33.0547312 165.754703,33.0583707 C154.184714,33.0824468 142.613886,33.1073628 131.044457,32.9953807 C123.828611,32.9256719 119.054815,28.2386618 118.644961,21.0474524 C118.443393,17.5107783 118.278219,13.9612262 119.264222,10.4900616 C120.893841,4.75209966 125.375644,1.25741881 131.351008,1.15159574 C134.993785,1.08692609 138.638802,1.13955767 142.360526,1.13955767 C142.067973,3.13647816 141.787458,5.04913214 141.487626,7.09476484 C140.08589,7.09476484 138.705151,7.09196529 137.324692,7.0956047 C135.969429,7.09924412 134.613326,7.07096865 133.260022,7.12611982 C130.810974,7.22550392 128.982867,8.4018757 128.056215,10.6524356 C127.537738,11.9113942 127.181635,13.3201288 127.138242,14.6737122 C127.057335,17.1947088 127.02486,19.7604983 127.40308,22.2423012 C127.84009,25.1115622 129.811534,26.8142497 132.692833,26.9881019 C136.08617,27.1927492 139.496585,27.1378779 142.89944,27.149916 C150.370045,27.1765118 157.840649,27.1767917 165.310694,27.1793113 C165.863886,27.1793113 166.427436,27.1669933 166.968309,27.0656495 C169.228667,26.6431971 170.52234,24.975224 170.33561,22.7789754 C170.15084,20.6026036 168.595409,19.1966685 166.270661,19.156355 C163.006103,19.0995241 159.735666,19.1737122 156.477268,19.0046193 C152.610246,18.8038914 149.808735,16.6286394 148.801456,13.3167693 C147.634882,9.48138298 148.701512,5.44134938 151.492945,3.26693729 C153.719709,1.53205487 156.356607,0.813129899 159.08897,0.763857783 C165.758063,0.643197088 172.430235,0.698908175 179.101008,0.687430011 C179.199552,0.687150056 179.298376,0.734182531 179.508623,0.78737402" fill="#231F20" />
              <path d="M239.03491,70.9679451 C239.026232,70.9256719 239.017553,70.8831187 239.008595,70.8405655 C237.511114,71.0505319 235.971081,71.1084826 234.527632,71.5121781 C233.031551,71.930991 232.336702,73.337486 232.47276,74.9690649 C232.588942,76.3663214 233.420129,77.4987402 234.929087,77.6389978 C236.480599,77.7834546 238.09762,77.6557951 239.638774,77.3808791 C242.218841,76.9209127 242.219121,76.8318869 242.195045,74.1860302 C242.185526,73.162514 242.451484,71.8097704 241.916489,71.2157055 C241.445045,70.6924692 240.032111,71.0177772 239.03491,70.9679451 L239.03491,70.9679451 Z M249.993477,83.6804311 C247.400532,83.2109462 244.814586,82.6977884 242.211002,82.3005319 C241.487598,82.1902296 240.702044,82.3713606 239.961842,82.5085386 C236.802548,83.0925252 233.647172,83.468785 230.471081,82.6303191 C226.701484,81.6350784 224.559826,78.9827828 224.431047,75.1423572 C224.297508,71.1605543 226.136534,68.3092105 229.983959,67.2339026 C232.258595,66.5984043 234.680767,66.4351904 237.051148,66.2154255 C238.66425,66.0662094 240.302268,66.1871501 241.90977,66.1871501 C242.688886,62.8819989 241.166209,60.8198488 237.882335,60.7621781 C234.266433,60.6989082 230.793869,61.5135778 227.394373,62.6835106 C227.072144,62.7946529 226.743477,62.887318 226.318785,63.0191769 C226.026792,61.0421333 225.745437,59.137598 225.453443,57.1597144 C231.072144,55.3582027 236.654451,53.7369821 242.605459,54.924832 C246.947844,55.7918533 249.252436,58.2926932 249.807867,62.6546753 C249.842301,62.925112 249.930207,63.1891097 249.993477,63.4559071 L249.993477,83.6804311 Z" fill="#231F20" />
              <path d="M158.794401,82.7700728 L150.616629,82.7700728 L150.616629,81.4895577 C150.616629,76.2773516 150.618589,71.0648656 150.61383,65.8526596 C150.61327,65.2975084 150.61243,64.737598 150.546641,64.187766 C150.357671,62.6104983 149.335834,61.5393897 147.79804,61.3725364 C145.92766,61.1701288 144.137626,61.4887178 142.655543,62.2546753 C142.709015,64.549748 142.792161,66.7149216 142.803919,68.8806551 C142.827156,73.1197368 142.810638,77.3593785 142.810638,81.5984602 L142.810638,82.7711926 L134.651904,82.7711926 L134.651904,81.5718645 C134.651904,76.3596585 134.603191,71.1466125 134.667581,65.9352464 C134.717693,61.8770157 132.899104,60.8224244 129.099832,61.4898376 C126.821277,61.8901736 126.831355,61.9489642 126.831355,64.2283595 L126.831355,81.4288074 L126.831355,82.7345185 L118.532083,82.7345185 L118.532083,81.4783595 C118.532083,74.7370381 118.529843,67.9959966 118.532923,61.2543953 C118.534602,57.8364222 119.402464,56.6818869 122.715454,55.775112 C126.810358,54.6544513 130.961814,54.1057391 135.17514,54.9461646 C136.310358,55.1726484 137.417301,55.6636898 138.459015,56.1880459 C139.015286,56.4680011 139.407503,56.5267917 139.975812,56.28743 C143.99037,54.5967805 148.138186,54.0774636 152.401904,55.1191769 C156.144345,56.0337906 158.48785,58.6648096 158.625868,62.5049552 C158.817637,67.8495801 158.753247,73.2031635 158.793001,78.5528275 C158.80308,79.9355263 158.794401,81.318505 158.794401,82.7700728" fill="#231F20" />
              <path d="M211.558567,69.0847984 L211.558567,65.025168 C211.558567,61.3040034 211.558567,61.3040034 207.795689,60.9702968 C204.886954,60.7121781 202.768533,62.1225924 202.028611,64.9361422 C201.346361,67.5299272 201.35,70.1631859 202.046529,72.7544513 C202.820605,75.6340705 204.792329,77.087598 207.773012,77.0324468 C208.668309,77.0159295 209.558847,76.8177212 210.455263,76.7488522 C211.300168,76.6841825 211.613998,76.3328387 211.582363,75.4336226 C211.507335,73.3196809 211.558287,71.2015398 211.558567,69.0847984 M211.558567,55.7597704 C211.558567,55.2793673 211.558287,54.9112262 211.558567,54.5433651 C211.558567,51.1393897 211.566685,47.7356943 211.551288,44.3319989 C211.548768,43.787766 211.619877,43.412346 212.265733,43.3090426 C214.729619,42.9143057 217.187906,42.4848544 219.802128,42.0425252 L219.802128,43.2149776 C219.801848,54.3983483 219.804367,65.5819989 219.800168,76.7659295 C219.799328,79.968617 219.09776,80.9473404 216.045969,81.8851904 C212.068645,83.1071948 207.995857,83.4053471 203.893953,82.7947648 C197.963382,81.9115062 193.998656,77.4479003 193.525252,71.2194569 C193.321725,68.5450448 193.405711,65.8826708 194.10056,63.2594905 C195.494457,57.9960526 199.242777,54.8717525 204.675868,54.7617301 C206.520493,54.7242161 208.376316,55.1657055 210.22402,55.4109462 C210.622676,55.4638578 211.008735,55.6130739 211.558567,55.7597704" fill="#231F20" />
              <path d="M181.12682,71.175168 C178.746081,71.175168 176.555151,71.0326708 174.391937,71.2171613 C172.417973,71.3859742 171.290034,72.9262878 171.386058,74.8173852 C171.475084,76.5707447 172.745241,77.8960526 174.731523,77.799748 C176.718365,77.7031635 178.68729,77.2034434 180.656495,76.8392217 C180.841265,76.8050672 181.102184,76.4775196 181.106383,76.281551 C181.142217,74.5864222 181.12682,72.8898936 181.12682,71.175168 M180.958007,66.1961646 C181.227044,62.024832 179.940649,60.6508119 175.963046,60.762234 C172.673572,60.8543393 169.50224,61.5942609 166.401176,62.6653695 C166.078108,62.7767917 165.74972,62.8742161 165.272396,63.0265118 C165.006159,61.1620101 164.734602,59.3585386 164.512878,57.5489082 C164.492161,57.3795353 164.75112,57.0581467 164.940929,56.9971165 C169.415454,55.5609462 173.926372,54.3022676 178.699608,54.5489082 C179.426932,54.5864222 180.157055,54.6606103 180.8743,54.7846305 C186.246641,55.7149216 188.790314,58.6617301 188.833987,64.1508119 C188.883819,70.4052912 188.846865,76.6608903 188.845745,82.9159295 C188.845745,83.1216965 188.824748,83.3274636 188.807111,83.6508119 C187.891097,83.4951568 187.006159,83.4052912 186.153695,83.1883259 C182.818029,82.3397816 179.510918,82.1695689 176.093785,82.9016517 C173.645017,83.4262878 171.110582,83.2487962 168.68729,82.4120101 C165.305991,81.2443169 163.475924,78.7275196 163.369261,75.1155375 C163.260918,71.4324468 164.863942,68.8431411 168.157615,67.4548432 C170.402856,66.5077548 172.788634,66.2633539 175.192609,66.2054031 C177.093505,66.1592105 178.996641,66.1961646 180.958007,66.1961646" fill="#231F20" />
              <polygon fill="#289AD6" points="0 35.3082307 105.710246 35.3082307 105.710246 0.279955207 0 0.279955207" />
              <polygon fill="#85B740" points="0 83.0573908 105.710246 83.0573908 105.710246 48.0291153 0 48.0291153" />
            </g>
          </g>
        </svg>
      </span>
    );
  }

  return (
    <span className={`spc-card-type ${classValue}`} />
  );
};

export default CardTypeSVG;
