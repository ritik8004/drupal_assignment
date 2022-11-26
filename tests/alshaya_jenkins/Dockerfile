# Documentation https://github.com/jenkinsci/docker/blob/master/README.md
# https://www.digitalocean.com/community/tutorials/how-to-automate-jenkins-setup-with-docker-and-jenkins-configuration-as-code
# Example https://github.com/ByteFader/ci-tool-stack/blob/67e1fe6e3a91344a8ea27bdd8bfef693d50e828f/jenkins/Dockerfile
FROM jenkins/jenkins:latest

# Install system requirements.
#USER root
#RUN apt-get update \
#      && apt-get install -y sudo npm php8.1-cli
#RUN curl -L https://getcomposer.org/composer-2.phar --output ~/composer-2.phar \
#    && chmod +x ~/composer-2.phar \
#    && mv ~/composer-2.phar /usr/local/bin/composer \
#    && composer --version
#USER jenkins

# Prepare behat suite.
#RUN cd /behat && composer install
#RUN cd /behat && npm install

# Install plugins.
# @todo configure plugins
RUN jenkins-plugin-cli --plugins pipeline-model-definition github-branch-source cucumber-reports blueocean

# Add default jobs.
COPY jobs/uat-smoke-scheduled.xml /usr/share/jenkins/ref/jobs/uat-smoke-scheduled/config.xml
COPY jobs/local-smoke-pull-requests.xml /usr/share/jenkins/ref/jobs/local-smoke-pull-requests.xml/config.xml
