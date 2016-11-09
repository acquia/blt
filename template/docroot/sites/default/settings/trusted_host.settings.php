<?php

if ($is_ah_env) {
  if ($is_ah_prod_env) {
    $settings['trusted_host_patterns'] = array(
      '^example\.com$',
    );
  }
  elseif ($is_ah_stage_env) {
    $settings['trusted_host_patterns'] = array(
      '^stg\.example\.com$',
    );
  }
  elseif ($is_ah_dev_env) {
    $settings['trusted_host_patterns'] = array(
      '^dev\.example\.com$',
    );
  }
}
