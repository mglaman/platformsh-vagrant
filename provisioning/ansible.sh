#!/bin/sh
ANSIBLE_STABLE_BRANCH=stable-1.9
which ansible > /dev/null 2>&1

if [ $? -eq 1 ]; then
  echo "Installing Ansible build dependencies."
  apt-get -qq --force-yes update > /dev/null 2>&1
  apt-get -qq --force-yes install git python-setuptools python-dev > /dev/null 2>&1

  branch="--branch $ANSIBLE_STABLE_BRANCH"
  ansible_dir=/usr/local/lib/ansible/

  if [ ! -d $ansible_dir ]; then
    echo "Cloning Ansible."
    git clone --quiet --recursive git://github.com/ansible/ansible.git $branch $ansible_dir > /dev/null 2>&1
  fi

  echo "Running setups tasks for Ansible."
  cd $ansible_dir
  python ./setup.py install > /dev/null 2>&1
fi
