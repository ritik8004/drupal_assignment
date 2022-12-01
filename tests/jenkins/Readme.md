# Docker Jenkins
Automated pipelines

### Pipelines syntax
https://www.jenkins.io/doc/book/pipeline/syntax/#stages

### Building and starting
make build

### Opening Jenkins on browser
make open

### TODO
- Update Readme
- Create library to reuse for executing pipelines https://www.jenkins.io/doc/book/pipeline/shared-libraries/
  - Arguments Brand, Env, Market, etc
  - Move Pipeline script into a Pipeline file
- Uninstall plugins that comes from image but we don't need
- Add build number to screenshots folder to avoid losing images when another test runs
- Delete screenshots when builds are deleted
- Move nginx configurations to jenkins or appserver container
-
