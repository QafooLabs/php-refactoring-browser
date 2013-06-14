Feature: Fix Class Names
    In order to fix class names to comply to PSR-0
    As a developer
    I need a fix class name command that finds and fixes all classes

    Scenario: "Class renamed in the same namespace"
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
        Then the PHP File "src/Foo/Bar.php" should be refactored:
            """
            --- a/Foo/Bar.php
            +++ b/Foo/Bar.php
            @@ -2,5 +2,5 @@
             namespace Foo;

            -class Foo
            +class Bar
             {
             }
            """

    Scenario: "Class moved to different namespace"
        Given a PHP File named "src/Foo/Bar.php" with:
            """
            <?php
            namespace Baz;

            class Bar
            {
            }
            """
        When I use refactoring "fix-class-names" with:
            | arg   | value |
            | dir   | src/  |
        Then the PHP File "src/Foo/Bar.php" should be refactored:
            """
            --- a/Foo/Bar.php
            +++ b/Foo/Bar.php
            @@ -1,4 +1,4 @@
             <?php
            -namespace Baz;
            +namespace Foo;

             class Bar
            """
    Scenario: "Class renamed changes use statements"
        Given a PHP File named "src/Foo/Bar.php" with:
            """
            <?php
            namespace Foo;

            class Foo
            {
            }
            """
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            use Foo\Foo;
            """
        When I use refactoring "fix-class-names" with:
            | arg   | value |
            | dir   | src/  |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/Foo.php
            +++ b/Foo.php
            @@ -1,2 +1,2 @@
             <?php
            -use Foo\Foo;
            +use Foo\Bar;
            """
