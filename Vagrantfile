# -*- mode: ruby -*-
# vi: set ft=ruby :
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "geerlingguy/ubuntu1404"
  config.ssh.insert_key = false

  config.vm.provider :virtualbox do |v|
    v.name = "platformsh"
    v.memory = 4096
    v.cpus = 4
    v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    v.customize ["modifyvm", :id, "--ioapic", "on"]
  end

  config.vm.hostname = "platformsh.dev"
  config.vm.network :private_network, ip: "10.22.22.100"

  config.vm.define :platformsh do |platformsh|
  end

  config.vm.synced_folder "./project", "/var/platformsh", id: "sites", type: "nfs"

  config.vm.provision "shell",
    path: "provisioning/ansible-install.sh",
    keep_color: true

  config.vm.provision "shell",
    inline: "sudo ansible-galaxy install -r /vagrant/provisioning/requirements > /dev/null 2>&1",
    keep_color: true

    # Ansible provisioner.
  config.vm.provision "shell",
    inline: "PYTHONUNBUFFERED=1 ANSIBLE_FORCE_COLOR=true ansible-playbook /vagrant/provisioning/playbook.yml -i /vagrant/provisioning/inventory --connection=local --sudo",
    keep_color: true

end
