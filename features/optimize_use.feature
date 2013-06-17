Feature: Optimize use
    To optimize the use statements in my code
    As a developer
    I need to convert every FQN in the code to a use statement in the file

    Scenario: Convert FQN and leave relative names
        Given a PHP File named "src/Foo.php" with:
            """
            <?php

            namespace ACME;

            use ACME\Baz\Service;

            class Foo
            {
                public function operation()
                {
                    $flag = Qux\Adapter::CONSTANT_ARG;

                    $service = new Service();
                    $service->operation(new \ACME\Qux\Adapter($flag));

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
            @@ -1,11 +1,13 @@
             <?php
             class Foo

             namespace ACME;

             use ACME\Baz\Service;
            +use ACME\Qux\Adapter;

             {
                 public function operation()
                 {
                     $flag = Qux\Adapter::CONSTANT_ARG;

                     $service = new Service();

            -        $service->operation(new \ACME\Qux\Adapter($flag));
            +        $service->operation(new Adapter($flag));

                     return $service;
                 }
             }
            """

