Feature: Rename Property
    To keep my code base clean
    As a developer
    I want to rename properties

    Scenario: Rename Property using its declaration
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                private $number = 2;

                public function operation()
                {
                    $var = 2;

                    for ($i = 0; $i < 3; $i++) {
                        $var = pow($var, 2);
                    }

                    return $var * $this->number;
                }
            }
            """
        When I use refactoring "rename-property" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 4           |
            | name      | number      |
            | new-name  | magic       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,7 +1,7 @@
             <?php
             class Foo
             {
            -    private $number = 2;
            +    private $magic = 2;

                 public function operation()
                 {
            @@ -11,6 +11,6 @@
                         $var = pow($var, 2);
                     }

            -        return $var * $this->number;
            +        return $var * $this->magic;
                 }
             }
            """

    Scenario: Rename Property using a line where it's used.
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                private $number = 2;

                public function operation()
                {
                    $var = 2;

                    for ($i = 0; $i < 3; $i++) {
                        $var = pow($var, 2);
                    }

                    return $var * $this->number;
                }
            }
            """
        When I use refactoring "rename-property" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 14          |
            | name      | number      |
            | new-name  | magic       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,7 +1,7 @@
             <?php
             class Foo
             {
            -    private $number = 2;
            +    private $magic = 2;

                 public function operation()
                 {
            @@ -11,6 +11,6 @@
                         $var = pow($var, 2);
                     }

            -        return $var * $this->number;
            +        return $var * $this->magic;
                 }
             }
            """

    Scenario: Rename Property using any line inside the class.
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                private $number = 2;

                public function operation()
                {
                    $var = 2;

                    for ($i = 0; $i < 3; $i++) {
                        $var = pow($var, 2);
                    }

                    return $var * $this->number;
                }
            }
            """
        When I use refactoring "rename-property" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | line      | 7           |
            | name      | number      |
            | new-name  | magic       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            --- a/vfs://project/src/Foo.php
            +++ b/vfs://project/src/Foo.php
            @@ -1,7 +1,7 @@
             <?php
             class Foo
             {
            -    private $number = 2;
            +    private $magic = 2;

                 public function operation()
                 {
            @@ -11,6 +11,6 @@
                         $var = pow($var, 2);
                     }

            -        return $var * $this->number;
            +        return $var * $this->magic;
                 }
             }
            """