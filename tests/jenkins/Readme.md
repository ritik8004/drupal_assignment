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
- Uninstall plugins that comes from image, that we don't need
- Add build number to Results/Screenshots folder to keep results with jobs and avoid losing images/reports
  when another job runs. Delete screenshots when builds are deleted.
  Alternative solution is to move the results into the jobs folder.
- Make it possible to run builds in parallel. Reports and results need to be in to a folder that relates to
  the Job/build number. Same as previous point.
- Move Nginx configurations to Jenkins or Appserver container and remove Nginx container
- Use Configuration Matrix to run the multiple builds
- Make it work with Lando url for local development
- Provide a way to turn on debug on browser via parameter on job. This would call the rebuild command with
  argument --debug. Alter the build command to automatically remove the healess/disable-gpu lines.

