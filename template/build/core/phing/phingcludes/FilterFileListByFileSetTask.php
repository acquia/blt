<?php

/**
 * Returns the intersection of a file list and set of filesets.
 *
 * Example usage:
 *
 *  <filterFileListByFileSet fileList="${files}" returnProperty="filteredFileList" root="${repo.root}">
 *    <fileset refid="files.php.custom.modules"/>
 *    <fileset refid="files.php.custom.themes"/>
 *    <fileset refid="files.php.tests"/>
 *  </filterFileListByFileSet>
 */
require_once 'phing/Task.php';

class FilterFileListByFileSetTask extends Task {

  /**
   * The return value.
   *
   * @var null
   */
  protected $return_property = null;

  public function setFileList($fileList)
  {
    $this->fileList = $fileList;
  }

  /**
   * Nested adder, adds a set of files (nested fileset attribute).
   *
   * @param FileSet $fs
   * @return void
   */
  public function addFileSet(FileSet $fs)
  {
    $this->filesets[] = $fs;
  }

  /**
   * The Phing property the return code should be assigned to.
   *
   * @param string $str The Phing property.
   *
   * @return void
   */
  public function setReturnProperty($str)
  {
    $this->return_property = $str;
  }


  /**
   * The main entry point method.
   *
   * @throws BuildException
   * @return bool $return
   */
  public function main() {

    if (!isset($this->fileList)) {
      throw new BuildException("You must set the file property.");
    }

    if (count($this->filesets) == 0) {
      throw new BuildException("You must define a fileset.");
    }

    $this->fileListFiles = array_map(array($this, 'prependProjectPath'), explode("\n", $this->fileList));
    $this->fileSetFiles = $this->getFilesetFiles();
    $filteredList = array_intersect($this->fileSetFiles, $this->fileListFiles);

    // Return the Behat exit value to a Phing property if specified.
    if (!empty($this->return_property)) {
      $this->getProject()
        ->setProperty($this->return_property, implode($filteredList, ','));
    }

    return (bool) $filteredList;
  }

  protected function prependProjectPath($relative_path) {
    return $this->project->getBasedir()->getAbsolutePath() . DIRECTORY_SEPARATOR . $relative_path;
  }

  /**
   * Return the list of files to parse
   *
   * @see PhpCodeSnifferTask
   *
   * @return string[] list of absolute files to parse
   */
  protected function getFilesetFiles()
  {
    $files = array();

    foreach ($this->filesets as $fs) {
      $dir = $fs->getDir($this->project)->getAbsolutePath();
      foreach ($fs->getDirectoryScanner($this->project)->getIncludedFiles() as $filename) {
        $file_path = $dir . DIRECTORY_SEPARATOR . $filename;
        $files[] = $file_path;
      }
    }

    return $files;
  }
}
