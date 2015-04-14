Feature: Convert Local to Instance Variable
    To keep the my code base clean
    As a developer
    I want to convert local variables to instance variables

    Scenario: Convert Variable
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                public function operation()
                {
                    $service = new Service();
                    $service->operation();

                    return $service;
                }
            }
            """
        When I use refactoring "convert-local-to-instance-variable" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 6           |
            | variable  | service     |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,11 +1,13 @@
             <?php
             class Foo
             {
            +    private $service;
            +
                 public function operation()
                 {
            -        $service = new Service();
            -        $service->operation();
            +        $this->service = new Service();
            +        $this->service->operation();

            -        return $service;
            +        return $this->service;
                 }
             }
            """

