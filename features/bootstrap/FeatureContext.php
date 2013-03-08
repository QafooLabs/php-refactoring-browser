<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use org\bovigo\vfs\vfsStream;

use Symfony\Component\Console\Input\ArrayInput;

use QafooLabs\Refactoring\Application\CliApplication;

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
    }

    /**
     * @Given /^a PHP File named "([^"]*)" with:$/
     */
    public function aPhpFileNamedWith($file, PyStringNode $code)
    {
        $parts = explode("/", $file);
        $structure = array();
        $root = &$structure;

        while ($part = array_shift($parts)) {
            $structure = array($part => (string)$code);
            $structure = &$structure[$part];
        }

        vfsStream::create($root, $this->root);
    }

    /**
     * @When /^I use refactoring "([^"]*)" with:$/
     */
    public function iUseRefactoringWith($refactoringName, TableNode $table)
    {
        $data = array('command' => $refactoringName);
        foreach ($table->getHash() as $line) {
            $data[$line['arg']] = $line['value'];
        }

        $input = new ArrayInput($data);

        $app = new CliApplication();
        $app->setAutoExit(false);
        $app->run($input);
    }

    /**
     * @Then /^the PHP File "([^"]*)" should be refactored:$/
     */
    public function thePhpFileShouldBeRefactored($arg1, PyStringNode $string)
    {
        throw new PendingException();
    }

}
