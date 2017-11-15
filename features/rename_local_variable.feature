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
            @@ -3,12 +3,12 @@
             {
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

    Scenario: Rename variable in method other other similarly named variables on same line
        Given a PHP File named "src/SimilarVariableName.php" with:
            """
            <?php
            class SimilarVariableName
            {
                public function operation()
                {
                    $var = 2;

                    $varsecond = 5;

                    return $var + $varsecond;
                }
            }
            """
        When I use refactoring "rename-local-variable" with:
            | arg       | value                       |
            | file      | src/SimilarVariableName.php |
            | line      | 6                           |
            | name      | var                         |
            | new-name  | number                      |
        Then the PHP File "src/SimilarVariableName.php" should be refactored:
            """
            --- a/vfs://project/src/SimilarVariableName.php
            +++ b/vfs://project/src/SimilarVariableName.php
            @@ -3,10 +3,10 @@
             {
                 public function operation()
                 {
            -        $var = 2;
            +        $number = 2;
       
                     $varsecond = 5;
       
            -        return $var + $varsecond;
            +        return $number + $varsecond;
                 }
             }

            """

    Scenario: Rename a method argument
        Given a PHP File named "src/MethodArgument.php" with:
            """
            <?php
            class MethodArgument
            {
                public function operation($varsecond = 5)
                {
                    $var = 2;

                    return $var + $varsecond;
                }
            }
            """
        When I use refactoring "rename-local-variable" with:
            | arg       | value                  |
            | file      | src/MethodArgument.php |
            | line      | 4                      |
            | name      | varsecond              |
            | new-name  | multiplier             |
        Then the PHP File "src/MethodArgument.php" should be refactored:
            """
            --- a/vfs://project/src/MethodArgument.php
            +++ b/vfs://project/src/MethodArgument.php
            @@ -1,10 +1,10 @@
             <?php
             class MethodArgument
             {
            -    public function operation($varsecond = 5)
            +    public function operation($multiplier = 5)
                 {
                     $var = 2;

            -        return $var + $varsecond;
            +        return $var + $multiplier;
                 }
             }

            """

    Scenario: Rename Variable In functions
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            function operation()
            {
                $var = 2;

                for ($i = 0; $i < 3; $i++) {
                    $var = pow($var, 2);
                }

                return $var * $var;
            }
            """
        When I use refactoring "rename-local-variable" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 4           |
            | name      | var         |
            | new-name  | number      |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,11 +1,11 @@
             <?php
             function operation()
             {
            -    $var = 2;
            +    $number = 2;

                 for ($i = 0; $i < 3; $i++) {
            -        $var = pow($var, 2);
            +        $number = pow($number, 2);
                 }

            -    return $var * $var;
            +    return $number * $number;
             }
            """

    Scenario: Rename a function's parameter
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            function operation($number)
            {
                $var = 2;

                for ($i = 0; $i < 3; $i++) {
                    $var = pow($var, $number);
                }

                return $var * $var;
            }
            """
        When I use refactoring "rename-local-variable" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 2           |
            | name      | number      |
            | new-name  | power       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,10 +1,10 @@
             <?php
            -function operation($number)
            +function operation($power)
             {
                 $var = 2;

                 for ($i = 0; $i < 3; $i++) {
            -        $var = pow($var, $number);
            +        $var = pow($var, $power);
                 }

                 return $var * $var;
            """