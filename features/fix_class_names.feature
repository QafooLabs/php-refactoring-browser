Feature: Fix Class Names
    In order to fix class names to comply to PSR-0
    As a developer
    I need a fix class name command that finds and fixes all classes

    Scenario: "Class moved in the same namespace"
        Given a PHP File named "src/Foo/Bar.php" with:
            """
            <?php
            namespace Foo;

            class Foo
            {
            }
            """
        When I use refactoring "fix-class-names" with:
            | arg   | value |
            | dir   | src/  |
        Then the PHP File "foo" should be refactored:
            """
            --- a/vfs://project/src/Foo/Bar.php
            +++ a/vfs://project/src/Foo/Bar.php
            @@ -1,6 +1,6 @@
             <?php
             namespace Foo;
             
            -class Foo
            +class Bar
             {
             }
            """
