# PHP Refactoring Browser

    Note: This software is under development and in alpha state. Refactorings
    do not contain all necessary pre-conditions and might mess up your code.
    Check the diffs carefully before applying the patches.

[![Build Status](https://travis-ci.org/QafooLabs/php-refactoring-browser.png)](https://travis-ci.org/QafooLabs/php-refactoring-browser.png)

Automatic Refactorings for PHP Code by generating diffs that describe
the refactorings steps. To prevent simple mistakes during refactorings, an automated tool
is a great.

See a [screenshot of extract-method in action](docs/extract_method.png).

The library is standing on the shoulder of giants, using multiple existing libraries:

* [PHP Parser](https://github.com/nikic/PHP-Parser) by Nikic
* [PHP Token Reflection](https://github.com/Andrewsville/PHP-Token-Reflection) from Ondřej Nešpor
* [PHP Analyzer](https://github.com/scrutinizer-ci/php-analyzer) by Johannes Schmitt

Based on data from these sources the Refactoring Browser consists of two distinct components:

* ``Patches`` allows to build patches based on change operations on a file.
* ``Refactoring`` contains the actual Refactoring domain and adapters to third party libraries.

## Install & Basic Usage

[Download PHAR](http://qafoolabs.github.com/php-refactoring-browser/assets/refactor.phar)

The refactoring browser is used with:

    php refactor.phar <refactoring> <arg1>...<argN>

It outputs a diff to the screen and you can apply it to your code by piping it to ``patch -p1``:

    php refactor.phar <refactoring> <arg1>...<argN> | patch -p1

## Why?

Users of PHPStorm (or Netbeans) might wonder why this project exists, all the
refactorings are available in this IDE. We feel there are several reasons to have
such a tool in PHP natively:

* We are VIM users and don't want to use an IDE for refactorings. Also we
  are independent of an IDE and users of any (non PHP Storm) editor can now
  benefit from the practice of automated refactorings.
* The non-existance of a simple refactoring tool leads to programmers not
  refactoring "just to be safe". This hurts long time maintainability of code.
  Refactoring is one of the most important steps during development and just come easy.
* Generating patches for refactorings before applying them allows to easily
  verify the operation yourself or sending it to a colleague.
* The libaries (see above) to build such a tool are available, so why not do it.
* The project is an academic of sorts as well, as you can see in the Design Goals
  we try to be very strict about the Ports and Adapters architecture and a Domain
  Driven Design.

## Refactorings

### Extract Method

Extract a range of lines into a new method and call this method from the original
location:

    php refactor.phar extract-method <file> <line-range> <new-method>

This refactoring automatically detects all necessary inputs and ouputs from the
function and generates the argument list and return statement accordingly.

### Rename Local Variable

Rename a local variable from one to another name:

    php refactor.phar rename-local-variable <file> <line> <old-name> <new-name>

### Convert Local to Instance Variable

Converts a local variable into an instance variable, creates the property and renames
all the occurences in the selected method to use the instance variable:

    php refactor.phar convert-local-to-instance-variable <file> <line> <variable>

## Roadmap

Integration:

* Vim Plugin to apply refactorings from within Vim.

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

## Internals

### Design Goals

* Be independent of third-party libraries and any Type Inference Engine (PDepend, PHP Analyzer) via Ports+Adapters
* Apply Domain-Driven-Design and find suitable Bounded Contexts and Ubiquitous Language within them
* Avoid primitive obsession by introducing value objects for useful constructs in the domain

### Processing steps

When you run the Refactoring Browser the following steps happen:

* Update Type Database (based on filemtime or md5 hashes?) when necessary for refactoring
* Analyze Refactoring (pre conditions)
* Generate Patch to perform refactoring
* Optionally apply patch (Currently just pipe to patch)

