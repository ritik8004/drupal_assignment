#!/bin/bash

script_name=$0
script_full_path=$(dirname "$0")

deployed_branches=$($script_full_path/get-deployed-branches.sh | cut -d' ' -f2)
echo "Deployed branches:"
echo $deployed_branches
echo

to_delete=""
refs=$(git ls-remote -h alshaya@svn-25.enterprise-g1.hosting.acquia.com:alshaya.git | grep -o -E "refs/heads/.*-build$")
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
  read -p "Do you confirm the deletion of '$to_delete' branches? " -n 1 yn
  echo
  if [ "$yn" = y ] ; then
    for b in $to_delete ; do
      echo "Deleting $b"
      git push alshaya@svn-25.enterprise-g1.hosting.acquia.com:alshaya.git  :refs/heads/$b
    done
  else
    echo "Nothing deleted"
  fi
fi
