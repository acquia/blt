<?php

namespace Acquia\Blt\Robo\Commands\Solr;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "local" namespace.
 */
class ClearLocalSolrIndexCommand extends BltTasks {

  /**
   * Clear local solr index.
   *
   * @command solr:clear
   * @description Clears the local solr index.
   */
  public function clearSolr() {
    $this->taskExecStack()
      ->exec("curl -s http://local.ebsco.com:8983/solr/d8/update --data '<delete><query>*:*</query></delete>' -H 'Content-type:text/xml; charset=utf-8' > /dev/null")
      ->run();

    $this->taskExecStack()
      ->exec("curl -s http://local.ebsco.com:8983/solr/d8/update --data '<commit/>' -H 'Content-type:text/xml; charset=utf-8' > /dev/null")
      ->run();

    $this->say('<info>Cleared local solr index</info>');
  }

}
