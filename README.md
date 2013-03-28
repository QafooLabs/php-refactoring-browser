# PHP Refactoring Browser

Automatic Refactorings for PHP Code. The actual process is implemented using
multiple existing libraries:

* AST from Nikics [PHP Parser](https://github.com/nikic/PHP-Parser) project
* [PHP Token Reflection](https://github.com/Andrewsville/PHP-Token-Reflection) from Ondřej Nešpor
* [PHP Analyzer](https://github.com/scrutinizer-ci/php-analyzer) by Johannes Schmitt for Type Inference

Based on this the Browser contains two distinct components:

* ``Patches`` allows to build patches based on change operations on a file.
* ``Refactoring`` contains the actual Refactoring domain and adapters to third party libraries.

The refactoring browser is used with:

    php refactor.phar <refactoring> <arg1>...<argN>

## Refactorings

### Extract Method

Extract a range of lines into a new method and call this method from the original
location:

    php refactor.phar extract-method <file> <line-range> <new-method>

This refactoring automatically detects all necssary inputs and ouputs from the
function and generates the argument list and return statement accordingly.

### Rename Local Variable

Rename a local variable from one to another name:

    php refactor.phar rename-local-variable <file> <line> <old-name> <new-name>

### Convert Local to Instance Variable

Converts a local variable into an instance variable, creates the property and renames
all the occurances in the selected method to use the instance variable:

    php refactor.phar convert-local-to-instance-variable <file> <line> <variable>

## Roadmap

List of Refactorings to implement:

* Extract Method (Prototype Done)
    * Check code after line range if assignments are actually used, or just internal to extracted method
    * Check how previously defined arrays work when not fully part of extracted method.
* Rename Local Variable (Prototype Done)
* Optimize use statements
* Convert Local Variable to Instance Variable (Prototype Done)
* Convert Magic Value to Constant
* Rename Method
    * Private Methods Only first
* Rename Instance Variable
    * Private Variables Only First
* Rename Class (PSR-0 aware)
* Rename Namespace (PSR-0 aware)
* Extract Interface

## Design Goals

* Be independent of third-party libraries and any Type Inference Engine (PDepend, PHP Analyzer) via Ports+Adapters
* Apply Domain-Driven-Design and find suitable Bounded Contexts and Ubiquitous Language within them
* Avoid primitive obsession by introducing value objects for useful constructs in the domain

## Processing steps

When you run the Refactoring Browser the following steps happen:

* Update Type Database (based on filemtime or md5 hashes?) when necessary for refactoring
* Analyze Refactoring (pre conditions)
* Generate Patch to perform refactoring
* Optionally apply patch (Currently just pipe to patch)

