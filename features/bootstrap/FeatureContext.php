<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use org\bovigo\vfs\vfsStream;

use Symfony\Component\Console\Input\ArrayInput;

use QafooLabs\Refactoring\Adapters\Symfony\CliApplication;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $root;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->root = vfsStream::setup('project');
        $this->structure = array();
    }

    /**
     * @Given /^a PHP File named "([^"]*)" with:$/
     */
    public function aPhpFileNamedWith($file, PyStringNode $code)
    {
        $this->structure = $this->appendToTree($this->structure, $file, (string)$code);
    }

    private function appendToTree($tree, $path, $code)
    {
        @list($head, $rest) = explode("/", $path, 2); // Muting notice that happens when there are no 2 elements left.

        if (!isset($tree[$head])) {
            $tree[$head] = array();
        }

        if (empty($rest)) {
            $tree[$head] = $code;
        } else {
            $tree[$head] = $this->appendToTree($tree, $rest, $code);
        }

        return $tree;
    }

    /**
     * @When /^I use refactoring "([^"]*)" with:$/
     */
    public function iUseRefactoringWith($refactoringName, TableNode $table)
    {
        vfsStream::create($this->structure, $this->root);

        $data = array('command' => $refactoringName);
        foreach ($table->getHash() as $line) {
            $data[$line['arg']] = $line['value'];
        }

        if (isset($data['file'])) {
            $data['file'] = vfsStream::url('project/' . $data['file']);
        }
        if (isset($data['dir'])) {
            $data['dir'] = vfsStream::url('project/' . $data['dir']);
        }

        $data['--verbose'] = true;

        $fh = fopen("php://memory", "rw");
        $input = new ArrayInput($data);
        $output = new \Symfony\Component\Console\Output\StreamOutput($fh);

        $app = new CliApplication();
        $app->setAutoExit(false);
        $app->run($input, $output);

        rewind($fh);
        $this->output = stream_get_contents($fh);
    }

    /**
     * @Then /^the PHP File "([^"]*)" should be refactored:$/
     */
    public function thePhpFileShouldBeRefactored($file, PyStringNode $expectedPatch)
    {
        $output = array_map('trim', explode("\n", rtrim($this->output)));
        $formattedExpectedPatch = $this->formatExpectedPatch((string)$expectedPatch);

        assertEquals(
            $formattedExpectedPatch,
            $output,
            "Expected File:\n" . (string)$expectedPatch . "\n\n" .
            "Refactored File:\n" . $this->output . "\n\n" .
            "Diff:\n" . print_r(array_diff($formattedExpectedPatch, $output), true)
        );
    }

    /**
     * converts / paths in expectedPatch text to \ paths
     * 
     * leaves the a/ b/ slashes untouched
     * returns an array of lines
     * @return array
     */
    protected function formatExpectedPatch($patch) 
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $formatLine = function ($line) {
                if (0 === strpos($line, '---') || 0 === strpos($line, '+++')) {
                    $line = preg_replace('~/(?<!(a|b)/)~', '\\', $line);
                }

                return trim($line);
            };

        } else {
            $formatLine = function ($line) {
                return trim($line);
            };
        }

        return array_map($formatLine, explode("\n", rtrim($patch)));
    }
}
