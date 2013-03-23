Feature: Extract Method
    In order to extract a list of statements into its own method
    As a developer
    I need an extract method refactoring

    Scenario: "Extract side effect free line into method"
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                public function operation()
                {
                    echo "Hello World";
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | range     | 6-6         |
            | newmethod | hello       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            @@ -4,5 +4,10 @@
                 public function operation()
                 {
            -        echo "Hello World";
            +        $this->hello();
                 }
            +
            +    private function hello()
            +    {
            +        echo "Hello World";
            +    }
             }
            """

    Scenario: "Extract side effect free line from static method"
        Given a PHP File named "src/Foo.php" with:
            """
            <?php
            class Foo
            {
                public static function operation()
                {
                    echo "Hello World";
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value       |
            | file      | src/Foo.php |
            | range     | 6-6         |
            | newmethod | hello       |
        Then the PHP File "src/Foo.php" should be refactored:
            """
            @@ -4,5 +4,10 @@
                 public static function operation()
                 {
            -        echo "Hello World";
            +        self::hello();
                 }
            +
            +    private static function hello()
            +    {
            +        echo "Hello World";
            +    }
             }
            """

    Scenario: "Extract Method with instance variable"
        Given a PHP File named "src/WithInstance.php" with:
            """
            <?php
            class WithInstance
            {
                private $hello = 'Hello World!';
                public function test()
                {
                    echo $this->hello;
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                |
            | file      | src/WithInstance.php |
            | range     | 7-7                  |
            | newmethod | printHello           |
        Then the PHP File "src/WithInstance.php" should be refactored:
            """
            @@ -5,5 +5,10 @@
                 public function test()
                 {
            -        echo $this->hello;
            +        $this->printHello();
                 }
            +
            +    private function printHello()
            +    {
            +        echo $this->hello;
            +    }
             }
            """

    Scenario: "Extract Method with local variable"
        Given a PHP File named "src/WithLocal.php" with:
            """
            <?php
            class WithLocal
            {
                public function test()
                {
                    $hello = 'Hello World!';
                    echo $hello;
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                |
            | file      | src/WithLocal.php |
            | range     | 7-7                  |
            | newmethod | printHello           |
        Then the PHP File "src/WithLocal.php" should be refactored:
            """
            @@ -5,5 +5,10 @@
                 {
                     $hello = 'Hello World!';
            -        echo $hello;
            +        $this->printHello($hello);
                 }
            +
            +    private function printHello($hello)
            +    {
            +        echo $hello;
            +    }
             }
            """

    Scenario: "Extract Method with formatting that requires line range correction"
        Given a PHP File named "src/MultiLineCorrection.php" with:
            """
            <?php
            class MultiLineCorrection
            {
                public function test()
                {
                    foo(
                        "bar"
                    );
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/MultiLineCorrection.php |
            | range     | 6-8                         |
            | newmethod | foo                         |
        Then the PHP File "src/MultiLineCorrection.php" should be refactored:
            """
            @@ -4,7 +4,12 @@
                 public function test()
                 {
            -        foo(
            -            "bar"
            -        );
            +        $this->foo();
                 }
            +
            +    private function foo()
            +    {
            +        foo(
            +            "bar"
            +        );
            +    }
             }
            """

    Scenario: "Extract Method with one assignment returns value"
        Given a PHP File named "src/Assignment.php" with:
            """
            <?php
            class Assignment
            {
                public function test()
                {
                    $var = "foo";
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/Assignment.php |
            | range     | 6-6                         |
            | newmethod | foo                         |
        Then the PHP File "src/Assignment.php" should be refactored:
            """
            @@ -4,5 +4,12 @@
                 public function test()
                 {
            -        $var = "foo";
            +        $var = $this->foo();
                 }
            +
            +    private function foo()
            +    {
            +        $var = "foo";
            +
            +        return $var;
            +    }
             }
            """

    Scenario: Extract Method with multiple assignments
        Given a PHP File named "src/MultiAssignment.php" with:
            """
            <?php
            class MultiAssignment
            {
                public function test()
                {
                    $var = 'foo';
                    $var2 = 'baz';
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/MultiAssignment.php |
            | range     | 6-7                         |
            | newmethod | foo                         |
        Then the PHP File "src/MultiAssignment.php" should be refactored:
            """
            @@ -4,6 +4,13 @@
                 public function test()
                 {
            -        $var = 'foo';
            -        $var2 = 'baz';
            +        list($var, $var2) = $this->foo();
                 }
            +
            +    private function foo()
            +    {
            +        $var = 'foo';
            +        $var2 = 'baz';
            +
            +        return array($var, $var2);
            +    }
             }
            """

    Scenario: Extract method with multiple assignments of same variable
        Given a PHP File named "src/MultiAssignment.php" with:
            """
            <?php
            class MultiAssignment
            {
                public function test()
                {
                    $var = 'foo';
                    $var = 'baz';
                }
            }
            """
        When I use refactoring "extract-method" with:
            | arg       | value                       |
            | file      | src/MultiAssignment.php |
            | range     | 6-7                         |
            | newmethod | foo                         |
        Then the PHP File "src/MultiAssignment.php" should be refactored:
            """
            @@ -4,6 +4,13 @@
                 public function test()
                 {
            -        $var = 'foo';
            -        $var = 'baz';
            +        $var = $this->foo();
                 }
            +
            +    private function foo()
            +    {
            +        $var = 'foo';
            +        $var = 'baz';
            +
            +        return $var;
            +    }
             }
            """
