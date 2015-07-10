#!/bin/sh
ANSIBLE_STABLE_BRANCH=stable-1.9

if [ "$(id -u)" != "0" ]; then
  echo "Sorry, this script must be run as root."
  exit 1
fi

which ansible > /dev/null 2>&1
if [ $? -eq 1 ]; then

  echo "Installing Ansible build dependencies."
  apt-get --force-yes update
  apt-get --force-yes install git python-setuptools python-dev

  echo "Using default stable branch: $ANSIBLE_STABLE_BRANCH."
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
