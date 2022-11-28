FROM jenkins/jenkins:latest

USER root
RUN apt-get update && apt-get install -y apt-transport-https
RUN apt-get install ca-certificates curl gnupg lsb-release -y
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
RUN echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu focal stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

RUN apt-get update
# Install Docker
RUN apt-get install docker-ce docker-ce-cli containerd.io -y
RUN apt-get install docker-compose-plugin -y

RUN ssh-keygen -t rsa -P "" -f /root/.ssh/id_rsa
RUN apt-get install openjdk-11-jdk -y

# Install Jenkins plugins
# Docs
# https://packagist.org/packages/cawolf/behat-cucumber-json-formatter
# https://plugins.jenkins.io/cucumber-reports/
RUN jenkins-plugin-cli --plugins pipeline-model-definition github-branch-source cucumber-reports rebuild blueocean
RUN jenkins-plugin-cli --plugins conditional-buildstep pipeline-graph-analysis pipeline-stage-view jira slack simple-theme-plugin

# Copy scripts
COPY scripts/behat-build.sh /usr/share/jenkins/ref/scripts/behat-build.sh
COPY scripts/behat-run.sh /usr/share/jenkins/ref/scripts/behat-run.sh

# Copy default jobs
COPY jobs/adhoc-pipeline/config.xml /usr/share/jenkins/ref/jobs/adhoc-pipeline/config.xml
