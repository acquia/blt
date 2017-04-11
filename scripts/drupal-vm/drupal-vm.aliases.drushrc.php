// Local environment.
$aliases['${project.machine_name}.local'] = array(
  'root' => '/var/www/${project.machine_name}/docroot',
  'uri' => '${project.local.uri}',
  );
// Add remote connection options when alias is used outside VM.
if ('vagrant' != $_SERVER['USER']) {
  $aliases['${project.machine_name}.local'] += array(
    'remote-host' => '${project.local.hostname}',
    'remote-user' => 'vagrant',
    'ssh-options' => '-o PasswordAuthentication=no -i ' . drush_server_home() . '/.vagrant.d/insecure_private_key'
  );
}
