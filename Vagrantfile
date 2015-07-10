# -*- mode: ruby -*-
# vi: set ft=ruby :
VAGRANTFILE_API_VERSION = "2"

require 'yaml'

dir = File.dirname(File.expand_path(__FILE__))

configValues = YAML.load_file("#{dir}/config.yml")
platform     = configValues['platformsh']
data         = configValues['vagrantfile']

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "geerlingguy/ubuntu1404"
  config.ssh.insert_key = false
  config.ssh.forward_agent = true

  config.vm.provider :virtualbox do |v|
    v.name = "#{platform['project_name']}" + "." + "#{data['vm']['hostname_base']}"
    v.memory = "#{data['vm']['memory']}"
    v.cpus = "#{data['vm']['cpus']}"
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--ioapic", "on"]
  end

  config.vm.hostname = "#{platform['project_name']}" + "." + "#{data['vm']['hostname_base']}"
  config.vm.network :private_network, ip: "#{data['vm']['network']['private_network']}"

  config.vm.define :platformsh do |platformsh|
  end

  # We disable the synced folder on first boot, so we can put the platform project in the proper folder, and then share it.
  config.vm.synced_folder "./project", "/var/platformsh", id: "project", type: "nfs"

  config.vm.provision "shell",
    path: "provisioning/ansible-install.sh",
    keep_color: true

  config.vm.provision "shell",
    inline: "sudo ansible-galaxy install -f -r /vagrant/provisioning/requirements  > /dev/null 2>&1",
    keep_color: true

    # Ansible provisioner.
  config.vm.provision "shell",
    inline: "PYTHONUNBUFFERED=1 ANSIBLE_FORCE_COLOR=true ansible-playbook /vagrant/provisioning/playbook.yml -i /vagrant/provisioning/inventory --connection=local --sudo",
    keep_color: true
end
