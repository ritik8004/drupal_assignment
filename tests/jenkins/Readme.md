# Docker Jenkins
Automated pipelines with Jenkins running in Docker containers.
Can be used to trigger builds that can be queued up using AdHoc builds or Scheduled.
After builds are finished, it will provide Cucumber reports and Screenshots.

## Requirements
- Docker

## Configuring
Edit `.env` and specify the path to the behat folder. Defaults to relative path `../behat`,
but it can be an absolute path.

## Running
Run `make start build` to start the project. Wait few seconds for the containers to start.

## Opening Jenkins on browser
Run `make open` or open the browser on `http://127.0.0.1:8080/`

## Viewing the execution on Browser
- Open Finder and click menu Go -> Connect to server
- Comment out these two lines from `behat.yml`
  - "--headless"
  - "--disable-gpu"
- Enter address `vnc://127.0.0.1:5901`
- Run the jobs and observe the browser

## Technical information

### Pipelines syntax
https://www.jenkins.io/doc/book/pipeline/syntax/#stages

### Next steps / Todo
- Create library to reuse for executing pipelines https://www.jenkins.io/doc/book/pipeline/shared-libraries/
  - Arguments Brand, Env, Market, etc
  - Move Pipeline script into a Pipeline file
- Use Configuration Matrix to run the multiple builds

