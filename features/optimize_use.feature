Feature: Optimize use
    To optimize the use statements in my code
    As a developer
    I need to convert every FQN in the code to a use statement in the file

    Scenario: Convert FQN and leave relative names
        Given a PHP File named "src/Foo.php" with:
            """
            <?php

            namespace Bar;

            use Bar\Baz\Service;

            class Foo
            {
                public function operation()
                {
                    $flag = Qux\Adapter::CONSTANT_ARG;

                    $service = new Service();
                    $service->operation(new \Bar\Qux\Adapter($flag));

                    return $service;
                }
            }
            """
        When I use refactoring "optimize-use" with:
            | arg       | value       |
            | file      | src/Foo.php |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -3,6 +3,7 @@
             namespace Bar;

             use Bar\Baz\Service;
            +use Bar\Qux\Adapter;

             class Foo
             {
            @@ -11,7 +12,7 @@
                     $flag = Qux\Adapter::CONSTANT_ARG;

                     $service = new Service();
            -        $service->operation(new \Bar\Qux\Adapter($flag));
            +        $service->operation(new Adapter($flag));

                     return $service;
                 }
            """
    Scenario: Organize use for file without namespace and other uses
        Given a PHP File named "src/Foo.php" with:
            """
            <?php

            class Foo
            {
                public function operation()
                {
                    return new \Bar\Qux\Adapter();
                }
            }
            """
        When I use refactoring "optimize-use" with:
            | arg       | value       |
            | file      | src/Foo.php |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,9 +1,11 @@
             <?php
            +
            +use Bar\Qux\Adapter;

             class Foo
             {
                 public function operation()
                 {
            -        return new \Bar\Qux\Adapter();
            +        return new Adapter();
                 }
             }
            """
