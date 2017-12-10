### Local setup of Behat:
  * Clone alshaya git repo
  * cd alshaya_behat
  * composer install
  * npm install --prefix bin chromedriver
  * (In a separate terminal window) java -Dwebdriver.chrome.driver=bin/node_modules/chromedriver/bin/chromedriver -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar
  * bin/behat features/hmkw/manual/basket.feature --profile=(hmuat,hmqa,mckwqa,mckwuat)