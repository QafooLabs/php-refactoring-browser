Feature: Rename Local Variable
    To keep my code base clean
    As a developer
    I want to rename local variables

    Scenario: Rename Variable In Methods
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                public function operation()
                {
                    $var = 2;

                    for ($i = 0; $i < 3; $i++) {
                        $var = pow($var, 2);
                    }

                    return $var * $var;
                }
            }
            """
        When I use refactoring "rename-local-variable" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 6           |
            | name      | var         |
            | new-name  | number      |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -4,11 +4,11 @@
                 public function operation()
                 {
            -        $var = 2;
            +        $number = 2;

                     for ($i = 0; $i < 3; $i++) {
            -            $var = pow($var, 2);
            +            $number = pow($number, 2);
                     }

            -        return $var * $var;
            +        return $number * $number;
                 }
             }
            """
