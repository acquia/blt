<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

include_once 'phing/tasks/system/condition/Condition.php';

/**
 * Compares two files for equality based on size and
 * content. Timestamps are not at all looked at.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system.condition
 */
class FileInFilesetCondition extends ProjectComponent implements Condition
{
  /**
   * A php source code filename or directory
   *
   * @var PhingFile
   */
  protected $file; // the source file (from xml attribute)

  /**
   * All fileset objects assigned to this task
   *
   * @var FileSet[]
   */
  protected $filesets = array(); // all fileset objects assigned to this task

  /**
   * File to be performed syntax check on
   * @param PhingFile $file
   */
  public function setFile(PhingFile $file)
  {
    $this->file = $file;
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

  /**
   * comparison method of the interface
   *
   * @return bool if the files are equal
   * @throws BuildException if it all went pear-shaped
   */
  public function evaluate()
  {
    if (!isset($this->file)) {
      throw new BuildException("You must set the file property.");
    }

    if ($this->file instanceof PhingFile) {
      $this->file->getPath();
    } else {
      throw new BuildException("Could not load specified file.");
    }

    if (count($this->filesets) == 0) {
      throw new BuildException("You must define a fileset.");
    }

    $files = $this->getFilesetFiles();

    return array_search($this->file->getPath(), $files);
  }
}
