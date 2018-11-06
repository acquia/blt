<?php

namespace Acquia\Blt\Robo\Commands\Saml;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Helper\FormatterHelper;

/**
 * Defines commands in the "phpmd:*" namespace.
 */
class PhpmdCommand extends BltTasks {

    protected $bltRoot;
    protected $repoRoot;
    protected $deployDir;
    /**
     * @var \Symfony\Component\Console\Helper\FormatterHelper
     */
    protected $formatter;

    /**
     * This hook will fire for all commands in this command file.
     *
     * @hook init
     */
    public function initialize() {
        $this->bltRoot = $this->getConfigValue('blt.root');
        $this->repoRoot = $this->getConfigValue('repo.root');
        $this->deployDir = $this->getConfigValue('deploy.dir');
        $this->formatter = new FormatterHelper();
    }

    /**
     * Initializes SimpleSAMLphp for project.
     *
     * @command recipes:simplesamlphp:init
     * @aliases rsi saml simplesamlphp:init
     */
    public function initializeSimpleSamlPhp() {
        $this->requireModule();
        $this->initializeConfig();
        $this->setSimpleSamlPhpInstalled();
        $this->symlinkDocrootToLibDir();
        $this->addHtaccessPatch();
        $this->outputCompleteSetupInstructions();
    }

    /**
     * Adds phpmd as a dependency.
     *
     * @throws \Acquia\Blt\Robo\Exceptions\BltException
     */
    protected function requireModule() {
        $this->say('Adding PHP Mess Detector  module as a dependency...');
        $package_options = [
            'package_name' => 'phpmd/phpmd',
            'package_version' => '^2.6.0',
        ];
        $this->invokeCommand('internal:composer:require', $package_options);
    }

    /**
     * Copies configuration templates from PhpMS to the repo root.
     *
     * @throws \Acquia\Blt\Robo\Exceptions\BltException
     */
    protected function initializeConfig() {
        $destinationDirectory = "{$this->repoRoot}/";

        $this->say("Copying config files to ${destinationDirectory}...");
        $result = $this->taskFileSystemStack()
            ->copy("{$this->bltRoot}/src/Robo/Commands/Validate/phpmd-standards.xml", "${destinationDirectory}/phpmd-standards.xml", TRUE)
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
            ->run();
        if (!$result->wasSuccessful()) {
            throw new BltException("Unable to copy PHP Mess Detector config files.");
        }
    }

    /**
     * Sets value in blt.yml to let targets know phpmd is installed.
     * @throws \Acquia\Blt\Robo\Exceptions\BltException
     */
    protected function setSimpleSamlPhpInstalled() {
        $project_yml = $this->getConfigValue('blt.config-files.project');

        $this->say("Updating ${project_yml}...");

        $project_config = YamlMunge::parseFile($project_yml);
        $project_config['phpmd'] = TRUE;

        try {
            YamlMunge::writeFile($project_yml, $project_config);
        }
        catch (\Exception $e) {
            throw new BltException("Unable to update $project_yml.");
        }
    }

    /**
     * Executes PHP Mess Detector against all phpmd.filesets files.
     *
     * By default, these include custom themes, modules, and tests.
     *
     * @command tests:phpmd:scan:all
     *
     * @aliases phpmd tests:phpmd:scan validate:phpmd
     */
    public function sniffFileSets() {
        $bin = $this->getConfigValue('composer.bin');
        $result = $this->taskExecStack()
            ->dir($this->getConfigValue('repo.root'))
            ->exec("$bin/phpmd")
            ->run();
        $exit_code = $result->getExitCode();
        if ($exit_code) {
            $this->logger->notice('Try visiting `https://phpmd.org/rules/index.html` to learn more about the violations and how to fix them.');
            throw new BltException("PHPCS failed.");
        }
    }
    /**
     * Executes PHP Mess Detector against a list of files, if in phpcs.filesets.
     *
     * This command will execute PHP Codesniffer against a list of files if those
     * files are a subset of the phpcs.filesets filesets.
     *
     * @command tests:phpcs:sniff:files
     * @aliases tpsf
     *
     * @param string $file_list
     *   A list of files to scan, separated by \n.
     *
     * @return int
     */
    public function sniffFileList($file_list) {
        $this->say("Sniffing directories containing changed files...");
        $files = explode("\n", $file_list);
        $files = array_filter($files);
        $exit_code = $this->doSniffFileList($files);

        return $exit_code;
    }

    /**
     * Executes PHP Code Sniffer against an array of files.
     *
     * @param array $files
     *   A flat array of absolute file paths.
     *
     * @return int
     */
    protected function doSniffFileList(array $files) {
        if ($files) {
            $temp_path = $this->getConfigValue('repo.root') . '/tmp/phpcs-fileset';
            $this->taskWriteToFile($temp_path)
                ->lines($files)
                ->run();

            $bin = $this->getConfigValue('composer.bin') . '/phpmd';
            $bootstrap = __DIR__ . "/phpcs-validate-files-bootstrap.php";
            $command = "'$bin' --file-list='$temp_path' --bootstrap='$bootstrap' -l";
            if ($this->output()->isVerbose()) {
                $command .= ' -v';
            }
            elseif ($this->output()->isVeryVerbose()) {
                $command .= ' -vv';
            }
            $result = $this->taskExecStack()
                ->exec($command)
                ->printMetadata(FALSE)
                ->run();

            unlink($temp_path);

            return $result->getExitCode();
        }

        return 0;
    }
}
