#!/bin/bash

script_name=$0
script_full_path=$(dirname "$0")

echo "Fetching deployed branches. Please wait..."
branches_str=$($script_full_path/get-deployed-branches.sh)

# Avoid further processing if we can't fetch the list of deployed branches.
if [ $? -eq 1 ] ; then
  echo "Impossible to fetch deployed branches using Acquia Cloud API."
  exit
fi

# Find the branch names from the script's response (Stack %d - Env %s: %s).
deployed_branches=$(echo "$branches_str" | cut -d' ' -f6)
echo "$deployed_branches"

# Hardcode some branches for security. These branches are supposed to always be deployed at least on one env/stack.
deployed_branches+=" develop-build qa-build uat-build"
echo "Deployed branches:"
echo $deployed_branches
echo

scriptDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
bltDir="$scriptDir/../../blt"

repos=`grep -Ei '@svn-5975.enterprise-g1.hosting.acquia.com' ${bltDir}/blt.yml | sed -r "s/'//g" | sed -r "s/- //g"`

for repo in $repos ; do
  to_delete=""
  refs=$(git ls-remote -h $repo | grep -o -E "refs/heads/.*-build$")
  for ref in $refs ; do
    branch=$(echo $ref | cut -d'/' -f3)

    for deployed_branch in $deployed_branches ; do
      if [ "$branch" = "$deployed_branch" ] ; then
        break
      fi
    done

    if [ "$branch" = "$deployed_branch" ] ; then
      echo "Keeping $branch"
    else
      echo "Marking $branch as to be deleted"
      if [ "$to_delete" = "" ] ; then
        to_delete="$branch"
      else
        to_delete+=" $branch"
      fi
    fi
  done
  echo

  if [ ! "$to_delete" = "" ] ; then
    read -p "Do you confirm the deletion of '$to_delete' branches on $repo? " yn
    echo
    if [ "$yn" = y ] ; then
      for b in $to_delete ; do
        echo "Deleting $b"
        git push $repo  :refs/heads/$b
      done

      git remote prune $repo
    else
      echo "Nothing deleted"
    fi
  fi
done
