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

  config.vm.synced_folder "./project", "/var/platformsh", id: "sites", type: "nfs"

  config.vm.provision "shell",
    path: "provisioning/ansible-install.sh",
    keep_color: true

  config.vm.provisio1n "shell",
    inline: "sudo ansible-galaxy install -r /vagrant/provisioning/requirements > /dev/null 2>&1",
    keep_color: true

    # Ansible provisioner.
  config.vm.provision "shell",
    inline: "PYTHONUNBUFFERED=1 ANSIBLE_FORCE_COLOR=true ansible-playbook /vagrant/provisioning/playbook.yml -i /vagrant/provisioning/inventory --connection=local --sudo",
    keep_color: true

  # Build project.
  config.vm.provision "shell",
    inline: "cd /var && sudo rm -rf platformsh %% sudo /home/vagrant/.composer/vendor/platformsh/cli/platform get #{platform['project_id']} platformsh"
end
