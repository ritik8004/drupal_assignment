#!/bin/bash

scriptDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
bltDir="$scriptDir/../../blt"

repos=`grep -Ei '@svn-5975.enterprise-g1.hosting.acquia.com' ${bltDir}/blt.yml | sed -r "s/'//g" | tr -d "-"`

for repo in $repos ; do
  refs=$(git ls-remote -h $repo | grep -o -E "refs/heads/.*-build$")
  refs+=" distro"

  repo_name=$(echo $repo | cut -d '@' -f1)

  rm -Rf /tmp/$repo_name
  git clone $repo /tmp/$repo_name
  cd /tmp/$repo_name

  for ref in $refs ; do
    ref_name=$(echo $ref | cut -d '/' -f3)
    echo $ref_name

    git checkout $ref_name
    git pull --all
    git checkout --orphan $ref_name-tmp
    git add . --quiet
    git commit -m "Starting fresh orphan branch for $ref_name"
    git branch -D $ref_name
    git branch -m $ref_name

    git push -f origin $ref_name
  done
done
