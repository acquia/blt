// [vagrant_machine_name].local
$aliases['${project.machine_name}.local'] = array(
  // /var/www/${project.machine_name}/docroot
  'root' => '/var/www/${project.machine_name}/docroot',
  // vagrant_hostname
  'uri' => '${project.local.uri}',
  );
// Add connection options if alias is used outside VM.
if ('vagrant' != $_SERVER['USER']) {
  $aliases['${project.machine_name}.local'] += array(
    // vagrant_hostname
    'remote-host' => '${project.local.hostname}',
    'remote-user' => 'vagrant',
    'ssh-options' => '-o PasswordAuthentication=no -i ' . drush_server_home() . '/.vagrant.d/insecure_private_key'
  );
}
